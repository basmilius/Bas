<?php
/**
 * Copyright (c) 2017 - 2019 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Latte Framework package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Database\Query;

use Columba\Data\Collection;
use Columba\Database\Connection\Connection;
use Columba\Database\Error\DatabaseException;
use Columba\Database\Error\QueryException;
use Columba\Database\Model\Model;
use Columba\Database\Util\ErrorUtil;
use Generator;
use PDO;
use PDOStatement;
use function array_map;
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
	private PDOStatement $pdoStatement;
	private string $query;

	protected ?string $modelClass = null;
	protected ?array $modelArguments = null;

	/**
	 * Statement constructor.
	 *
	 * @param Connection $connection
	 * @param string     $query
	 * @param array      $options
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
	 * @param string   $name
	 * @param mixed    $value
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
	 * @param bool     $allowModel
	 * @param int|null $foundRows
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function array(bool $allowModel = true, ?int &$foundRows = null): array
	{
		$this->executeStatement($foundRows);

		return $this->fetchAll($allowModel);
	}

	/**
	 * Executes the {@see Statement} and returns a {@see CollectionResult}.
	 *
	 * @param bool     $allowModel
	 * @param int|null $foundRows
	 *
	 * @return Collection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function collection(bool $allowModel = true, ?int &$foundRows = null): Collection
	{
		$this->executeStatement($foundRows);

		return new Collection($this->fetchAll($allowModel));
	}

	/**
	 * Executes the {@see Statement} and returns a {@see Generator} containing each result.
	 *
	 * @param bool     $allowModel
	 * @param int|null $foundRows
	 *
	 * @return Generator
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function cursor(bool $allowModel = true, ?int &$foundRows = null): Generator
	{
		$this->executeStatement($foundRows);

		$result = new Result($this->connection, $this, $allowModel);

		while ($item = $result->yield())
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
	 * Executes the {@see Statement} and returns a single result.
	 *
	 * @param bool $allowModel
	 *
	 * @return Model|array|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function single(bool $allowModel = true)
	{
		$this->executeStatement();

		return $this->fetch($allowModel);
	}

	/**
	 * Fetches a single rows.
	 *
	 * @param bool $allowModel
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function fetch(bool $allowModel = true)
	{
		$result = $this->pdoStatement->fetch(PDO::FETCH_ASSOC);

		if ($result === false)
			return null;

		if ($this->modelClass !== null && $allowModel)
		{
			/** @var Model $class */
			$class = $this->modelClass;
			$arguments = $this->modelArguments ?? [];

			return $class::instance($result, $arguments);
		}

		return $result;
	}

	/**
	 * Fetches all rows.
	 *
	 * @param bool $allowModel
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function fetchAll(bool $allowModel = true): array
	{
		$results = $this->pdoStatement->fetchAll(PDO::FETCH_ASSOC);

		if ($this->modelClass !== null && $allowModel)
		{
			/** @var Model $class */
			$class = $this->modelClass;
			$arguments = $this->modelArguments ?? [];

			return array_map(fn(array $result) => $class::instance($result, $arguments), $results);
		}

		return $results;
	}

	/**
	 * Assigns a model to the query result.
	 *
	 * @param string|null $class
	 * @param array|null  $arguments
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
	 * @since 1.6.0
	 * @author Bas Milius <bas@mili.us>
	 */
	private function executeStatement(?int &$foundRows = null): void
	{
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
	 * @internal
	 */
	private function throwFromErrorInfo(): DatabaseException
	{
		[, $code, $message] = $this->pdoStatement->errorInfo();

		return ErrorUtil::throw($code, $message);
	}

}
