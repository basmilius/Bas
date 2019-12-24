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

namespace Columba\Database\Model;

use Columba\Database\Connection\Connection;
use Columba\Database\Db;
use Columba\Database\Error\ModelException;
use Columba\Database\Model\Relation\Relation;
use Columba\Database\Query\Builder\Builder;
use PDO;
use function array_keys;
use function array_map;
use function array_unshift;
use function implode;
use const JSON_BIGINT_AS_STRING;
use const JSON_HEX_AMP;
use const JSON_HEX_APOS;
use const JSON_HEX_QUOT;
use const JSON_HEX_TAG;

/**
 * Class Model
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Model
 * @since 1.6.0
 */
abstract class Model extends Base
{

	private static array $connections = [];
	private static array $initialized = [];

	protected static string $connectionId = 'default';

	public static string $order = 'ASC';
	public static string $orderBy = 'id';

	protected static string $primaryKey = 'id';
	protected static int $primaryKeyType = PDO::PARAM_INT;
	protected static string $table = '';

	protected static array $jsonColumns = [];
	protected static array $macros = [];
	protected static array $relationships = [];

	private array $relationCache = [];

	/**
	 * Model constructor.
	 *
	 * @param array $data
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(array $data)
	{
		if (!isset(static::$initialized[static::class]))
		{
			static::define();
			static::$jsonColumns[static::class] ??= [];
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

			if (isset(static::$jsonColumns[static::class][$column]) && $value !== null)
				$value = json_encode($value, JSON_BIGINT_AS_STRING | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG);
			else if (is_bool($value))
				$value = $value ? 1 : 0;

			$columnsAndValues[$column] = $value;
		}

		static::update($this->getValue(static::$primaryKey), $columnsAndValues);

		$this->modified = [];
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function prepare(array &$data): void
	{
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function publish(array &$data): void
	{
		foreach (static::$macros[static::class] as $name => $fn)
			$data[$name] = $fn($this);

		$data['@type'] = static::class;
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
		if (isset(static::$macros[static::class][$column]))
			throw new ModelException(sprintf('%s is a macro and is therefore immutable.', $column), ModelException::ERR_IMMUTABLE);

		parent::setValue($column, $value);
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function unsetValue(string $column): void
	{
		if (isset(static::$macros[static::class][$column]))
			throw new ModelException(sprintf('%s is a macro and is therefore immutable.', $column), ModelException::ERR_IMMUTABLE);

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
			$data[$macro] = $this->resolveMacro($macro);

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
	 * @param string      $column
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
	 * Gets a single model instance by primary key.
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
	 * Initiates a query builder with a SELECT ... HAVING expression assigned to the current model.
	 *
	 * @param mixed $column
	 * @param mixed $comparator
	 * @param mixed $value
	 * @param bool  $addParam
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
	 * @param array  $columns
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
	 * @param bool  $addParam
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
	 * @param array      $data
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
	 * @param string   $name
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
	 * @param string   $name
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
	 * @param array    $columns
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
	 * Gets the {@see Connection} instance based on our {@see self::$connectionId}.
	 *
	 * @return Connection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected static function connection(): Connection
	{
		return static::$connections[static::$connectionId] ??= Db::getOrFail(static::$connectionId);
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

	/**
	 * Decodes a JSON column and saves the column name for saving.
	 *
	 * @param string      $column
	 * @param string|null $json
	 *
	 * @return array|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected static function json(string $column, ?string $json = null): ?array
	{
		static::$jsonColumns[static::class][$column] = true;

		if ($json === null)
			return null;

		return json_decode($json, true);
	}

}
