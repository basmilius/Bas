<?php
/**
 * Copyright (c) 2019 - 2020 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Database\Query;

use Columba\Collection\ArrayList;
use Columba\Database\Connection\Connection;
use Columba\Database\Db;
use Columba\Database\Error\DatabaseException;
use Columba\Database\Error\QueryException;
use Columba\Database\Model\Model;
use Columba\Database\Model\ModelArrayList;
use Columba\Database\Model\Relation\Many;
use Columba\Database\Model\Relation\One;
use Columba\Database\Model\Relation\Relation;
use Columba\Database\Util\BuilderUtil;
use Columba\Database\Util\ErrorUtil;
use Generator;
use PDO;
use PDOStatement;
use function array_column;
use function array_filter;
use function array_map;
use function array_unique;
use function Columba\Database\Query\Builder\in;
use function is_float;
use function is_int;
use function is_subclass_of;
use function sprintf;
use function strpos;

/**
 * Class Statement
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Query
 * @since 1.6.0
 */
class Statement
{

	private Connection $connection;
	private array $eagerLoad = [];
	private PDOStatement $pdoStatement;
	private string $query;

	protected ?string $modelClass = null;
	protected ?array $modelArguments = null;

	/**
	 * Statement constructor.
	 *
	 * @param Connection $connection
	 * @param string $query
	 * @param array $options
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(Connection $connection, string $query, array $options = [])
	{
		$this->connection = $connection;
		$this->pdoStatement = $connection->getPdo()->prepare($query, $options);
		$this->query = $query;
	}

	/**
	 * Binds the given value.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param int|null $type
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function bind(string $name, $value, ?int $type = null): self
	{
		if ($type === null)
			$type = is_int($value) || is_float($value) ? PDO::PARAM_INT : PDO::PARAM_STR;

		$this->pdoStatement->bindValue($name, $value, $type);

		return $this;
	}

	/**
	 * Executes the {@see Statement} and returns an array containing all results.
	 *
	 * @param bool $allowModel
	 * @param int $fetchMode
	 * @param int|null $foundRows
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function array(bool $allowModel = true, int $fetchMode = PDO::FETCH_ASSOC, ?int &$foundRows = null): array
	{
		$this->executeStatement($foundRows);

		return $this->fetchAll($allowModel, $fetchMode);
	}

	/**
	 * Executes the {@see Statement} and returns an {@see ArrayList}.
	 *
	 * @param bool $allowModel
	 * @param int $fetchMode
	 * @param int|null $foundRows
	 *
	 * @return ModelArrayList|ArrayList
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function arrayList(bool $allowModel = true, int $fetchMode = PDO::FETCH_ASSOC, ?int &$foundRows = null): ArrayList
	{
		$allModels = true;
		$results = $this->array($allowModel, $fetchMode, $foundRows);

		foreach ($results as $result)
		{
			if ($result instanceof Model)
				continue;

			$allModels = false;
			break;
		}

		if ($allModels)
			return ModelArrayList::of($results);

		return ArrayList::of($results);
	}

	/**
	 * Executes the {@see Statement} and returns a {@see Generator} containing each result.
	 *
	 * @param bool $allowModel
	 * @param int $fetchMode
	 * @param int|null $foundRows
	 *
	 * @return Generator
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function cursor(bool $allowModel = true, int $fetchMode = PDO::FETCH_ASSOC, ?int &$foundRows = null): Generator
	{
		$this->executeStatement($foundRows);

		$result = new Result($this->connection, $this, $allowModel);

		while ($item = $result->yield($fetchMode))
			yield $item;
	}

	/**
	 * Executes the {@see Statement}.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function run(): void
	{
		$this->executeStatement();
	}

	/**
	 * Eager load the given relationships when the query is executed.
	 *
	 * @param string[] $relationships
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function eagerLoad(array $relationships): void
	{
		$this->eagerLoad = $relationships;
	}

	/**
	 * Executes the {@see Statement} and returns a single result.
	 *
	 * @param bool $allowModel
	 * @param int $fetchMode
	 *
	 * @return Model|array|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function single(bool $allowModel = true, int $fetchMode = PDO::FETCH_ASSOC)
	{
		$this->executeStatement();

		return $this->fetch($allowModel, $fetchMode);
	}

	/**
	 * Fetches a single rows.
	 *
	 * @param bool $allowModel
	 * @param int $fetchMode
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function fetch(bool $allowModel = true, int $fetchMode = PDO::FETCH_ASSOC)
	{
		$result = $this->pdoStatement->fetch($fetchMode);

		if ($result === false)
			return null;

		if ($this->modelClass !== null && $allowModel)
		{
			/** @var Model|string $model */
			$model = $this->modelClass;
			$arguments = $this->modelArguments ?? [];

			// todo(bas): figure out if this is necessary
			// if (!empty($this->eagerLoad))
			// 	$this->loadEagerLoadRelations($results, $model);

			return $model::instance($result, $arguments);
		}

		return $result;
	}

	/**
	 * Fetches all rows.
	 *
	 * @param bool $allowModel
	 * @param int $fetchMode
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function fetchAll(bool $allowModel = true, int $fetchMode = PDO::FETCH_ASSOC): array
	{
		$results = $this->pdoStatement->fetchAll($fetchMode);

		if ($this->modelClass !== null && $allowModel)
		{
			/** @var Model|string $model */
			$model = $this->modelClass;
			$arguments = $this->modelArguments ?? [];

			if (!empty($this->eagerLoad))
				$this->loadEagerLoadRelations($results, $model);

			return array_map(fn(array $result) => $model::instance($result, $arguments), $results);
		}

		return $results;
	}

	/**
	 * Prepares {@see One} and {@see Many} relations.
	 *
	 * @param Relation $relation
	 * @param array $results
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private function findEagerLoadKeys(Relation $relation, array $results): array
	{
		if (!($relation instanceof Many) && !($relation instanceof One))
			throw new QueryException('Not a One or Many relation.', QueryException::ERR_EAGER_NOT_AVAILABLE);

		$referenceKey = $relation->getReferenceKey();
		$selfKey = $relation->getSelfKey();

		$referenceKeyTrim = BuilderUtil::trimKey($referenceKey);
		$selfKeyTrim = BuilderUtil::trimKey($selfKey);

		$selfKeys = array_column($results, $selfKeyTrim);
		$selfKeys = array_filter($selfKeys, fn($val) => $val !== null && $val !== 0);
		$selfKeys = array_unique($selfKeys);

		return [
			$selfKeys,
			$referenceKeyTrim,
			$selfKeyTrim,
			$referenceKey,
			$selfKey
		];
	}

	/**
	 * Finds instances of the given model and filters them from the given keys array.
	 *
	 * @param Model|string $model
	 * @param array $keys
	 * @param ArrayList|null $results
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private function findLoadedInstancesByKeys(string $model, array &$keys, ?ArrayList &$results): void
	{
		$results = new ArrayList();

		foreach ($keys as $index => $key)
		{
			$instance = $model::connection()
				->getCache()
				->get($key, $model);

			if ($instance === null)
				continue;

			unset($keys[$index]);

			$results->add($instance);
		}
	}

	/**
	 * Eager loads relationships for the given results.
	 *
	 * @param array $results
	 * @param Model|string $model
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private function loadEagerLoadRelations(array &$results, string $model): void
	{
		$relations = $model::relations();

		if (empty($relations))
			return;

		foreach ($this->eagerLoad as $name)
		{
			$relation = $relations[$name] ?? null;

			if ($relation === null)
				continue;

			if ($relation instanceof Many)
				$this->loadEagerLoadMany($name, $relation, $results);

			if ($relation instanceof One)
				$this->loadEagerLoadOne($name, $relation, $results);
		}
	}

	/**
	 * Eager loads {@see Many} relationships.
	 *
	 * @param string $name
	 * @param Many $many
	 * @param array $results
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private function loadEagerLoadMany(string $name, Many $many, array &$results): void
	{
		$referenceModel = $many->getReferenceModel();
		[$selfKeys, $referenceKeyTrim, $selfKeyTrim] = $this->findEagerLoadKeys($many, $results);

		$this->findLoadedInstancesByKeys($referenceModel, $selfKeys, $instances);

		if (!empty($selfKeys))
		{
			$query = $referenceModel::select()
				->model($referenceModel)
				->where($many->getReferenceKey(), in($selfKeys));

			if (($eagerLoad = $many->getEagerLoad()) !== null)
				$query->eagerLoad($eagerLoad);

			$instances->merge($query->arrayList());
		}

		foreach ($results as &$result)
			if (($instance = $instances->filter(fn($v) => $v[$referenceKeyTrim] === $result[$selfKeyTrim]))->count() > 0)
				$result['_relations'][$name] = $instance;
	}

	/**
	 * Eager loads {@see One} relationships.
	 *
	 * @param string $name
	 * @param One $one
	 * @param array $results
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private function loadEagerLoadOne(string $name, One $one, array &$results): void
	{
		$referenceModel = $one->getReferenceModel();
		[$selfKeys, $referenceKeyTrim, $selfKeyTrim] = $this->findEagerLoadKeys($one, $results);

		$this->findLoadedInstancesByKeys($referenceModel, $selfKeys, $instances);

		if (!empty($selfKeys))
		{
			$query = $referenceModel::select()
				->model($referenceModel)
				->where($one->getReferenceKey(), in($selfKeys));

			if (($eagerLoad = $one->getEagerLoad()) !== null)
				$query->eagerLoad($eagerLoad);

			$instances->merge($query->arrayList());
		}

		if ($instances->count() === 0)
			return;

		foreach ($results as &$result)
			if (($instance = $instances->first(fn($v) => $v[$referenceKeyTrim] === $result[$selfKeyTrim])) !== null)
				$result['_relations'][$name] = $instance;
	}

	/**
	 * Assigns a model to the query result.
	 *
	 * @param string|null $class
	 * @param array|null $arguments
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @internal
	 */
	public function model(?string $class, ?array $arguments = []): self
	{
		if ($class === null)
		{
			$this->modelClass = null;
			$this->modelArguments = null;
		}
		else
		{
			if (!is_subclass_of($class, Model::class))
				throw new QueryException(sprintf('%s is not a subclass of %s.', $class, Model::class), QueryException::ERR_INVALID_MODEL);

			$this->modelClass = $class;
			$this->modelArguments = $arguments;
		}

		return $this;
	}

	/**
	 * Returns the amount of rows.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function rowCount(): int
	{
		return $this->pdoStatement->rowCount();
	}

	/**
	 * Gets the {@see PDOStatement}.
	 *
	 * @return PDOStatement
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @internal
	 */
	public final function getPdoStatement(): PDOStatement
	{
		return $this->pdoStatement;
	}

	/**
	 * Executes the current {@see PDOStatement}.
	 *
	 * @param int|null $foundRows
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private function executeStatement(?int &$foundRows = null): void
	{
		if ($this->modelClass === null && !empty($this->eagerLoad))
			throw new QueryException('Eager loading is only available on models.', QueryException::ERR_EAGER_NOT_AVAILABLE);

		if (Db::$enableQueryTracking)
			Db::$trackedQueries[] = $this->query;

		$result = $this->pdoStatement->execute();
		$foundRows = strpos($this->query, 'SQL_CALC_FOUND_ROWS') !== false ? $this->connection->foundRows() : null;

		if ($result === false)
			throw $this->throwFromErrorInfo();
	}

	/**
	 * Throws an error from {@see PDOStatement::errorInfo()}.
	 *
	 * @return DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private function throwFromErrorInfo(): DatabaseException
	{
		[, $code, $message] = $this->pdoStatement->errorInfo();

		return ErrorUtil::throw($code, $message);
	}

}
