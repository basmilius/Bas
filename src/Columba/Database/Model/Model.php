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

namespace Columba\Database\Model;

use Columba\Database\Cast\ICast;
use Columba\Database\Connection\Connection;
use Columba\Database\Db;
use Columba\Database\Error\ModelException;
use Columba\Database\Model\Relation\Relation;
use Columba\Database\Query\Builder\Builder;
use Columba\Util\ArrayUtil;
use PDO;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_search;
use function array_unshift;
use function implode;
use function in_array;

/**
 * Class Model
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Model
 * @since 1.6.0
 */
abstract class Model extends Base
{

	private static array $castInstances = [];
	private static array $connections = [];
	private static array $initialized = [];

	protected static string $connectionId = 'default';

	public static string $order = 'ASC';
	public static string $orderBy = 'id';

	protected static string $primaryKey = 'id';
	protected static int $primaryKeyType = PDO::PARAM_INT;
	protected static string $table = '';

	protected static array $casts = [];
	protected static array $macros = [];

	/** @var Relation[][] */
	protected static array $relationships = [];

	protected array $hidden = [];
	protected array $visible = [];

	private array $relationCache = [];

	/**
	 * Model constructor.
	 *
	 * @param array|null $data
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(array $data = null)
	{
		if (!isset(static::$initialized[static::class]))
		{
			static::define();

			static::$columns[static::class] ??= static::connection()->query()
				->select(['COLUMN_NAME'])
				->from('information_schema.COLUMNS')
				->where('TABLE_SCHEMA', static::connection()->getConnector()->getDatabase())
				->and('TABLE_NAME', static::table())
				->collection()
				->column('COLUMN_NAME')
				->toArray();
			static::$macros[static::class] ??= [];
			static::$relationships[static::class] ??= [];
			static::$initialized[static::class] = true;
		}

		parent::__construct($data);

		$this->cache();
	}

	/**
	 * Caches the model instance.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function cache(): void
	{
		if ($this->isNew)
			return;

		static::connection()->getCache()->set($this->getValue(static::$primaryKey), $this);
	}

	/**
	 * Removes the model instance from cache.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function cacheRemove(): void
	{
		static::connection()->getCache()->remove($this[static::$primaryKey], static::class);
	}

	/**
	 * Deletes the current model instance by its primary key.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function destroy(): void
	{
		self::delete($this[static::$primaryKey]);
	}

	/**
	 * Gets the connection instance.
	 *
	 * @return Connection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getConnection(): Connection
	{
		return static::connection();
	}

	/**
	 * Gets a relationship.
	 *
	 * @param string $name
	 *
	 * @return Relation|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getRelation(string $name): ?Relation
	{
		/** @var Relation $relationship */
		$relationship = static::$relationships[static::class][$name] ?? null;

		if ($relationship === null)
			return null;

		$relationship->setModel($this);

		return $relationship;
	}

	/**
	 * Marks the given columns as hidden.
	 *
	 * @param string[] $columns
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function makeHidden(array $columns): self
	{
		foreach ($columns as $column)
		{
			if (($key = array_search($column, $this->visible)) !== false)
				unset($this->visible[$key]);

			if (in_array($column, static::$columns[static::class]) && !in_array($column, $this->hidden))
				$this->hidden[] = $column;
		}

		return $this;
	}

	/**
	 * Marks the given columns as visible.
	 *
	 * @param string[] $columns
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function makeVisible(array $columns): self
	{
		foreach ($columns as $column)
		{
			if (($key = array_search($column, $this->hidden)) !== false)
				unset($this->hidden[$key]);

			if (!in_array($column, static::$columns[static::class]) && !in_array($column, $this->visible))
				$this->visible[] = $column;
		}

		return $this;
	}

	/**
	 * Returns only the given columns of the model instance.
	 *
	 * @param array $columns
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function only(array $columns): array
	{
		return ArrayUtil::only($this->toArray(), $columns);
	}

	/**
	 * Resolves a macro value.
	 *
	 * @param string $column
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function resolveMacro(string $column)
	{
		$macros = static::$macros[static::class];

		if (!isset($macros[$column]))
			return null;

		$macro = $macros[$column];
		$value = $macro($this);

		if ($value instanceof Relation)
			return $value->get();

		return $value;
	}

	/**
	 * Saves all modified fields.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function save(): void
	{
		$columnsAndValues = [];

		foreach ($this->modified as $column)
		{
			$value = $this->getValue($column);

			if (array_key_exists($column, static::$casts))
			{
				/** @var ICast $cast */
				$castClass = static::$casts[$column];
				$cast = self::$castInstances[$castClass] ?? self::$castInstances[$castClass] = new $castClass();

				$value = $cast->set($this, $column, $value, $this->modified);
			}

			$columnsAndValues[$column] = $value;
		}

		if ($this->isNew)
		{
			static::query()
				->insertIntoValues(static::table(), $columnsAndValues)
				->run();

			$newPrimaryKey = static::connection()->lastInsertIdInteger();

			$this->isNew = false;
			$this->afterInsert($newPrimaryKey);
			$this->initialize();
			$this->cache();
		}
		else
		{
			static::update($this->getValue(static::$primaryKey), $columnsAndValues);
		}

		$this->modified = [];
	}

	/**
	 * Executed after a new record is inserted to the database.
	 *
	 * @param int $newPrimaryKey
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function afterInsert(int $newPrimaryKey): void
	{
		$this->setData(
			static::where(static::column(static::$primaryKey), $newPrimaryKey)
				->model(null)
				->single()
		);
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function prepare(array &$data): void
	{
		foreach (static::$casts as $key => $castClass)
		{
			if (!array_key_exists($key, $data))
				continue;

			/** @var ICast $cast */
			$cast = self::$castInstances[$castClass] ?? self::$castInstances[$castClass] = new $castClass();

			$data[$key] = $cast->get($this, $key, $data[$key], $data);
		}
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function publish(array &$data): void
	{
		foreach (static::$macros[static::class] as $name => $fn)
			if (in_array($name, $this->visible) || in_array($name, static::$columns[static::class]))
				$data[$name] = $fn($this);

		foreach (array_keys(static::$relationships[static::class]) as $relation)
			if (in_array($relation, $this->visible))
				$data[$relation] = $this->getValue($relation);

		foreach ($this->hidden as $column)
			unset($data[$column]);
	}

	/**
	 * Throws an exception when the given column name is immutable.
	 *
	 * @param string $column
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private function checkImmutable(string $column): void
	{
		if (isset(static::$macros[static::class][$column]))
			throw new ModelException(sprintf('%s is a macro and is therefore immutable.', $column), ModelException::ERR_IMMUTABLE);

		if (isset(static::$relationships[static::class][$column]))
			throw new ModelException(sprintf('%s is a relationship and is therefore immutable.', $column), ModelException::ERR_IMMUTABLE);
	}

	/**
	 * Checks if the given column is used in a {@see Relation} and unsets it in cache.
	 *
	 * @param string $column
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private function checkRelations(string $column): void
	{
		foreach (static::$relationships[static::class] as $relationColumn => $relation)
			if (in_array($column, $relation->relevantColumns()))
				unset($this->relationCache[$relationColumn]);
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getValue(string $column)
	{
		if (isset(static::$macros[static::class][$column]))
			return $this->resolveMacro($column);

		if (isset($this->relationCache[$column]))
			return $this->relationCache[$column];

		$relation = $this->getRelation($column);

		if ($relation !== null)
			return $this->relationCache[$column] = $relation->get();

		return parent::getValue($column);
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function hasValue(string $column): bool
	{
		if (isset(static::$macros[static::class][$column]))
			return true;

		if (isset(static::$relationships[static::class][$column]))
			return true;

		return parent::hasValue($column);
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function setValue(string $column, $value): void
	{
		$this->checkImmutable($column);
		$this->checkRelations($column);

		parent::setValue($column, $value);
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function unsetValue(string $column): void
	{
		$this->checkImmutable($column);
		$this->checkRelations($column);

		parent::unsetValue($column);
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function toArray(): array
	{
		$data = parent::toArray();

		foreach (array_keys(static::$macros[static::class]) as $macro)
			if (in_array($macro, $this->visible) || in_array($macro, static::$columns[static::class]))
				$data[$macro] = $this->resolveMacro($macro);

		foreach (array_keys(static::$relationships[static::class]) as $relation)
			if (in_array($relation, $this->visible))
				$data[$relation] = $this->getValue($relation);

		return $data;
	}

	/**
	 * Returns all rows.
	 *
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return $this[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function all(int $offset = 0, int $limit = 20): array
	{
		return static::select()
			->orderBy(static::$orderBy . ' ' . static::$order)
			->limit($limit, $offset)
			->array();
	}

	/**
	 * Returns a fully qualified column name for the given column.
	 *
	 * @param string $column
	 * @param string|null $table
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function column(string $column, ?string $table = null): string
	{
		$table ??= static::$table;

		return static::connection()
			->getDialect()
			->escapeColumn($table . '.' . $column);
	}

	/**
	 * Gets the {@see Connection} instance based on our {@see self::$connectionId}.
	 *
	 * @return Connection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function connection(): Connection
	{
		return static::$connections[static::$connectionId] ??= Db::getOrFail(static::$connectionId);
	}

	/**
	 * Deletes an instance by its primary key.
	 *
	 * @param string|int $primaryKey
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function delete($primaryKey): void
	{
		self::query()
			->deleteFrom(static::$table)
			->where(static::$primaryKey, $primaryKey)
			->run();
	}

	/**
	 * Finds multiple instances by primary key.
	 *
	 * @param array $primaryKeys
	 *
	 * @return $this[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function find(array $primaryKeys): array
	{
		if (empty($primaryKeys))
			return [];

		$connection = static::connection();
		$primaryKeys = array_map(fn($primaryKey) => $connection->quote($primaryKey, static::$primaryKeyType), $primaryKeys);

		return static::where(static::column(static::$primaryKey), 'IN', '(' . implode(', ', $primaryKeys) . ')', false)
			->orderBy(static::$orderBy . ' ' . static::$order)
			->array();
	}

	/**
	 * Gets a single model instance by its primary key.
	 *
	 * @param string|int $primaryKey
	 *
	 * @return $this|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function get($primaryKey): ?self
	{
		$cache = static::connection()->getCache();

		if ($cache->has($primaryKey, static::class))
			return $cache->get($primaryKey, static::class);

		return static::where(self::column(static::$primaryKey), '=', $primaryKey)
			->collection()
			->first();
	}

	/**
	 * Gets a single model instance by its primary key and throw an exception if nothing was found.
	 *
	 * @param string|int $primaryKey
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function getOrFail($primaryKey): self
	{
		$model = static::get($primaryKey);

		if ($model === null)
			throw new ModelException(sprintf('Model with primary key "%s" not found.', strval($primaryKey)), ModelException::ERR_NOT_FOUND);

		return $model;
	}

	/**
	 * Initiates a query builder with a SELECT ... HAVING expression assigned to the current model.
	 *
	 * @param mixed $column
	 * @param mixed $comparator
	 * @param mixed $value
	 * @param bool $addParam
	 *
	 * @return Builder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function having(?string $column = null, $comparator = null, $value = null, bool $addParam = true): Builder
	{
		return static::select()
			->having($column, $comparator, $value, $addParam);
	}

	/**
	 * Initiates a query builder assigned to the current model.
	 *
	 * @return Builder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function query(): Builder
	{
		return (new Builder(static::connection()))
			->model(static::class);
	}

	/**
	 * Gets the primary key.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function primaryKey(): string
	{
		return static::$primaryKey;
	}

	/**
	 * Initiates a query builder with a SELECT clause assigned to the current model.
	 *
	 * @param array $columns
	 *
	 * @return Builder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function select(array $columns = []): Builder
	{
		return static::baseSelect(fn(array $cols) => static::query()->select($cols), $columns);
	}

	/**
	 * Initiates a query builder with a SELECT SQL_CALC_FOUND_ROWS clause assigned to the current model.
	 *
	 * @param array $columns
	 *
	 * @return Builder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function selectFoundRows(array $columns = []): Builder
	{
		return static::baseSelect(fn(array $cols) => static::query()->selectFoundRows($cols), $columns);
	}

	/**
	 * Initiates a query builder with a SELECT [$suffix] clause assigned to the current model.
	 *
	 * @param string $suffix
	 * @param array $columns
	 *
	 * @return Builder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function selectSuffix(string $suffix, array $columns = []): Builder
	{
		return static::baseSelect(fn(array $cols) => static::query()->selectSuffix($suffix, $cols), $columns);
	}

	/**
	 * Gets the table name.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function table(): string
	{
		return static::$table;
	}

	/**
	 * Starts a transaction on the {@see Connection} instance.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function transaction(): bool
	{
		return static::connection()->transaction();
	}

	/**
	 * Commit everything in the active transaction.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function transactionCommit(): bool
	{
		return static::connection()->commit();
	}

	/**
	 * Undo everything in the active transaction.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function transactionRollBack(): bool
	{
		return static::connection()->rollBack();
	}

	/**
	 * Updates columns of the model by primary key.
	 *
	 * @param mixed $primaryKey
	 * @param array $columnsAndValues
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function update($primaryKey, array $columnsAndValues): void
	{
		static::query()
			->updateValues(static::$table, $columnsAndValues)
			->where(static::column(static::$primaryKey), $primaryKey)
			->run();
	}

	/**
	 * Initiates a query builder with a SELECT ... WHERE expression assigned to the current model.
	 *
	 * @param mixed $column
	 * @param mixed $comparator
	 * @param mixed $value
	 * @param bool $addParam
	 *
	 * @return Builder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function where(?string $column = null, $comparator = null, $value = null, bool $addParam = true): Builder
	{
		return static::select()
			->where($column, $comparator, $value, $addParam);
	}

	/**
	 * Creates a model instance.
	 *
	 * @param array $data
	 * @param array|null $arguments
	 *
	 * @return static
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function instance(array $data, ?array $arguments = null): self
	{
		$cache = static::connection()->getCache();

		if ($cache->has($data[static::$primaryKey], static::class))
			return $cache->get($data[static::$primaryKey], static::class);

		$arguments ??= [];

		array_unshift($arguments, $data);

		return new static(...$arguments);
	}

	/**
	 * Adds a macro.
	 *
	 * @param string $name
	 * @param callable $fn
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function macro(string $name, callable $fn): void
	{
		static::$macros[static::class][$name] = $fn;
	}

	/**
	 * Defines a relationship.
	 *
	 * @param string $name
	 * @param Relation $relation
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function relation(string $name, Relation $relation): void
	{
		static::$relationships[static::class][$name] = $relation;
	}

	/**
	 * Base SELECT builder.
	 *
	 * @param callable $selectCallable
	 * @param array $columns
	 *
	 * @return Builder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected static function baseSelect(callable $selectCallable, array $columns): Builder
	{
		return $selectCallable($columns)
			->from(static::$table);
	}

	/**
	 * Defines the model, adds relationships and macros for example.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected static function define(): void
	{
	}

}
