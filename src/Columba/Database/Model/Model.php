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
use Columba\Database\Model\Event\AbstractEventListener;
use Columba\Database\Model\Event\CallablesEventListener;
use Columba\Database\Model\Relation\Relation;
use Columba\Database\Query\Builder\Builder;
use Columba\Util\ArrayUtil;
use PDO;
use function array_keys;
use function array_map;
use function array_unshift;
use function class_exists;
use function Columba\Database\Query\Builder\in;
use function Columba\Database\Query\Builder\literal;
use function get_class;
use function in_array;
use function is_array;
use function sprintf;

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

	private static array $casts = [];
	private static array $macros = [];
	private static array $macrosToCache = [];

	/** @var CallablesEventListener[] */
	private static array $listener = [];
	/** @var AbstractEventListener[][] */
	private static array $listeners = [];

	protected static string $connectionId = 'default';

	public static string $order = 'ASC';
	public static string $orderBy = 'id';

	protected static string $primaryKey = 'id';
	protected static int $primaryKeyType = PDO::PARAM_INT;
	protected static string $table = '';

	/** @var Relation[][] */
	protected static array $relationships = [];

	protected array $hidden = [];
	protected array $visible = [];

	private array $macroCache = [];
	private array $relationCache = [];

	/**
	 * Model constructor.
	 *
	 * @param array|null $data
	 * @param bool $isNew
	 * @param static|null $copyOf
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(array $data = null, bool $isNew = true, ?self $copyOf = null)
	{
		static::prepareModel();

		parent::__construct($data, $isNew, $copyOf);

		if (isset($this->modelData['_relations']))
		{
			$this->relationCache = $this->modelData['_relations'];
			unset($this->modelData['_relations']);
		}

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
		if ($this->isNew || !$this->hasValue(static::$primaryKey))
			return;

		static::connection()
			->getCache()
			->set($this->getValue(static::$primaryKey), $this);
	}

	/**
	 * Removes the model instance from cache.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function cacheRemove(): void
	{
		static::connection()
			->getCache()
			->remove($this[static::$primaryKey], static::class);
	}

	/**
	 * Deletes the current model instance by its primary key.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function destroy(): void
	{
		static::delete($this[static::$primaryKey]);
		static::trigger(fn(AbstractEventListener $listener) => $listener->onDeleted($this));
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
	 * @return Relation
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getRelation(string $name): Relation
	{
		/** @var Relation $relationship */
		$relationship = static::$relationships[static::class][$name] ?? null;

		if ($relationship === null)
			throw new ModelException(sprintf('The relation %s does not exist on %s.', $name, static::class), ModelException::ERR_RELATION_NOT_FOUND);

		$relationship->setModel($this);

		return $relationship;
	}

	/**
	 * Returns TRUE if the given relation exists.
	 *
	 * @param string $name
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function hasRelation(string $name): bool
	{
		return isset(static::$relationships[static::class][$name]);
	}

	/**
	 * Marks the given columns as hidden.
	 *
	 * @param string[] $columns
	 *
	 * @return static
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function makeHidden(array $columns): self
	{
		return $this->copyWith(function (self $model) use ($columns): void
		{
			$model->hidden = $this->hidden;
			$model->visible = $this->visible;

			foreach ($columns as $column)
			{
				if (($key = array_search($column, $model->visible)) !== false)
					unset($model->visible[$key]);

				if (!in_array($column, $model->hidden))
					$model->hidden[] = $column;
			}
		});
	}

	/**
	 * Marks the given columns as visible.
	 *
	 * @param string[] $columns
	 *
	 * @return static
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function makeVisible(array $columns): self
	{
		return $this->copyWith(function (self $model) use ($columns): void
		{
			$model->hidden = $this->hidden;
			$model->visible = $this->visible;

			foreach ($columns as $column)
			{
				if (($key = array_search($column, $model->hidden)) !== false)
					unset($model->hidden[$key]);

				if (!in_array($column, $model->visible))
					$model->visible[] = $column;
			}
		});
	}

	/**
	 * Returns only the given columns of the model instance.
	 *
	 * @param array $columns
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
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
		$macrosToCache = static::$macrosToCache[static::class];

		if (!isset($macros[$column]))
			return null;

		if (isset($this->macroCache[$column]))
			return $this->macroCache[$column];

		$macro = $macros[$column];
		$value = $macro($this);

		if (in_array($column, $macrosToCache))
			$this->macroCache[$column] = $value;

		if ($value instanceof Relation)
			return $this->relationCache[$column] = $value->get();

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
		$casters = static::$casts[static::class];
		$columnsAndValues = [];

		foreach ($this->modified as $column)
		{
			$value = $this->modelData[$column] ?? $this->getValue($column);

			if (isset($casters[$column]))
			{
				$castClass = $casters[$column];
				$value = static::caster($castClass)
					->encode($this, $column, $value, $this->modified);
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

			static::trigger(fn(AbstractEventListener $listener) => $listener->onInserted($this));

			$this->macroCache = [];
			$this->relationCache = [];
		}
		else
		{
			static::update($this->getValue(static::$primaryKey), $columnsAndValues);
			static::trigger(fn(AbstractEventListener $listener) => $listener->onUpdated($this));
		}

		$this->modified = [];
	}

	/**
	 * Runs the given function on the value of the given column.
	 *
	 * @param string $column
	 * @param callable $fn
	 *
	 * @return static
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function withColumn(string $column, callable $fn): self
	{
		if ($this->hasValue($column))
		{
			$value = $this->getValue($column);
			$value = $fn($value);

			// todo(Bas): Probably need another cache for this.
			$this->relationCache[$column] = $value;
		}

		return $this;
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
		$this->setModelData(
			static::where(static::column(static::$primaryKey), $newPrimaryKey)
				->model(null)
				->single()
		);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function hasColumn(string $column): bool
	{
		return static::connection()
			->tableHasColumn(static::table(), $column);
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function prepare(array &$data): void
	{
		if ($this->copyOf !== null)
			return;

		foreach (static::$casts[static::class] as $key => $caster)
		{
			if (!isset($data[$key]))
				continue;

			$data[$key] = static::caster($caster)
				->decode($this, $key, $data[$key], $data);
		}
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function publish(array &$data): void
	{
	}

	/**
	 * Applies registered macros and relationships to the given data.
	 *
	 * @param array $data
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private function applyMacrosAndRelations(array &$data): void
	{
		$did = [];

		$macros = array_keys(static::$macros[static::class]);
		$relationships = array_keys(static::$relationships[static::class]);

		foreach ($macros as $macro)
		{
			if (in_array($macro, $did))
				continue;

			if (in_array($macro, $this->visible) || $this->hasColumn($macro))
			{
				$data[$macro] = $this->resolveMacro($macro);
				$did[] = $macro;
			}
		}

		foreach ($relationships as $relation)
		{
			if (in_array($relation, $did))
				continue;

			if (in_array($relation, $this->visible))
			{
				$data[$relation] = $this->relationCache[$relation] ??= $this->getValue($relation);
				$did[] = $relation;
			}
		}
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
		if (isset(static::$macros[static::class][$column]) && !$this->hasColumn($column))
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

		if ($this->hasRelation($column))
			return $this->relationCache[$column] ??= $this->getRelation($column)->get();

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

		$this->applyMacrosAndRelations($data);

		foreach ($this->hidden as $column)
			unset($data[$column]);

		return $data;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function serialize(): string
	{
		return serialize([
			$this->modelData,
			$this->hidden,
			$this->visible,
			$this->macroCache
		]);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function unserialize($serialized): void
	{
		[
			$this->modelData,
			$this->hidden,
			$this->visible,
			$this->macroCache
		] = unserialize($serialized);
	}

	/**
	 * Returns all rows.
	 *
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return static[]
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
	 * Returns all rows as a {@see ModelArrayList}.
	 *
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return ModelArrayList<static>
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function allModelArrayList(int $offset = 0, int $limit = 20): ModelArrayList
	{
		return static::select()
			->orderBy(static::$orderBy . ' ' . static::$order)
			->limit($limit, $offset)
			->arrayList();
	}

	/**
	 * Returns the instance of the given caster class.
	 *
	 * @param string $castClass
	 *
	 * @return ICast
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private static function caster(string $castClass): ICast
	{
		if (!class_exists($castClass))
			throw new ModelException(sprintf('Caster class %s not found.', $castClass), ModelException::ERR_CASTER_NOT_FOUND);

		return static::$castInstances[$castClass] ??= new $castClass;
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
	 * Gets the {@see Connection} instance based on our {@see static::$connectionId}.
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
		static::query()
			->deleteFrom(static::$table)
			->where(static::$primaryKey, $primaryKey)
			->run();
	}

	/**
	 * Returns TRUE if the given primary key exists.
	 *
	 * @param $primarykey
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function exists($primarykey): bool
	{
		$cache = static::connection()->getCache();

		if ($cache->has($primarykey, static::class))
			return true;

		if (is_int($primarykey))
			$primarykey = literal($primarykey);

		return static::select([1])
				->where(static::column(static::$primaryKey), $primarykey)
				->model(null)
				->single() !== null;
	}

	/**
	 * Finds multiple instances by primary key.
	 *
	 * @param array $primaryKeys
	 *
	 * @return static[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function find(array $primaryKeys): array
	{
		if (empty($primaryKeys))
			return [];

		return static::where(static::column(static::$primaryKey), in($primaryKeys))
			->orderBy(static::$orderBy . ' ' . static::$order)
			->array();
	}

	/**
	 * Gets a single model instance by its primary key.
	 *
	 * @param string|int $primaryKey
	 *
	 * @return static|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function get($primaryKey): ?self
	{
		$cache = static::connection()->getCache();

		if ($cache->has($primaryKey, static::class))
			return $cache->get($primaryKey, static::class);

		if (is_int($primaryKey))
			$primaryKey = literal($primaryKey);

		return static::where(static::column(static::$primaryKey), $primaryKey)
			->single();
	}

	/**
	 * Gets a single model instance by its primary key and throw an exception if nothing was found.
	 *
	 * @param string|int $primaryKey
	 *
	 * @return static
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function getOrFail($primaryKey): self
	{
		$model = static::get($primaryKey);

		if ($model === null)
			throw new ModelException(sprintf('Model with primary key "%s" not found.', (string)$primaryKey), ModelException::ERR_NOT_FOUND);

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
		return static::connection()
			->query()
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
		return static::connection()
			->transaction();
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
		return static::connection()
			->commit();
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
		return static::connection()
			->rollBack();
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
		$cache = static::connection()
			->getCache();

		if ($cache->has($data[static::$primaryKey], static::class))
			return $cache->get($data[static::$primaryKey], static::class);

		$arguments ??= [];

		array_unshift($arguments, $data, false);

		return new static(...$arguments);
	}

	/**
	 * Casts the given column with the given caster class.
	 *
	 * @param string $column
	 * @param string $casterClass
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function cast(string $column, string $casterClass): void
	{
		static::$casts[static::class][$column] = $casterClass;
	}

	/**
	 * Casts the given columns.
	 *
	 * @param array $casts
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function casts(array $casts): void
	{
		foreach ($casts as $column => $casterClass)
			static::$casts[static::class][$column] = $casterClass;
	}

	/**
	 * Marks the given macro as cacheable.
	 *
	 * @param string $macro
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function cacheMacro(string $macro): void
	{
		static::$macrosToCache[static::class][] = $macro;
	}

	/**
	 * Marks the given macros as cacheable.
	 *
	 * @param string[] $macros
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function cacheMacros(array $macros): void
	{
		foreach ($macros as $macro)
			static::$macrosToCache[static::class][] = $macro;
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
	 * Prepares the model before it's used.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @internal
	 */
	public static function prepareModel(): void
	{
		if (isset(static::$initialized[static::class]))
			return;

		static::define();

		static::$casts[static::class] ??= [];
		static::$listener[static::class] ??= new CallablesEventListener();
		static::$listeners[static::class] ??= [static::$listener[static::class]];
		static::$macros[static::class] ??= [];
		static::$macrosToCache[static::class] ??= [];
		static::$relationships[static::class] ??= [];
		static::$initialized[static::class] = true;
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
	 * Gets all defined relationships of the model.
	 *
	 * @return Relation[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @internal
	 */
	public static function relations(): array
	{
		static::prepareModel();

		return static::$relationships[static::class] ?? [];
	}

	/**
	 * Adds the given listener instance to the model.
	 *
	 * @param AbstractEventListener $listener
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function on(AbstractEventListener $listener): void
	{
		static::prepareModel();
		static::$listeners[static::class][] = $listener;
	}

	/**
	 * Executes the given function when a model instance is deleted.
	 *
	 * @param callable $fn
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function onDeleted(callable $fn): void
	{
		static::prepareModel();
		static::$listener[static::class]->addDeletedListener($fn);
	}

	/**
	 * Executes the given function when a model instance is inserted.
	 *
	 * @param callable $fn
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function onInserted(callable $fn): void
	{
		static::prepareModel();
		static::$listener[static::class]->addInsertedListener($fn);
	}

	/**
	 * Executes the given function when a model instance is updated.
	 *
	 * @param callable $fn
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function onUpdated(callable $fn): void
	{
		static::prepareModel();
		static::$listener[static::class]->addUpdatedListener($fn);
	}

	/**
	 * Executes the given function on all listeners.
	 *
	 * @param callable $fn
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected static function trigger(callable $fn): void
	{
		foreach (static::$listeners[static::class] as $listener)
			$fn($listener);
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

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __debugInfo(): array
	{
		$data = parent::__debugInfo();

		if (isset($data['_meta']))
		{
			$data['_meta']['casts'] = static::$casts[static::class];
			$data['_meta']['connection_id'] = static::$connectionId;
			$data['_meta']['macros'] = array_map(fn($macro) => is_array($macro) ? sprintf('%s::%s', $macro[0], $macro[1]) : get_class($macro), static::$macros[static::class]);
			$data['_meta']['macros_to_cache'] = static::$macrosToCache[static::class];
			$data['_meta']['relationships'] = array_map(fn($relation) => get_class($relation), static::$relationships[static::class]);
			$data['_meta']['relationships_resolved'] = array_keys($this->relationCache);
			$data['_meta']['visibility'] = [
				'hidden' => $this->hidden,
				'visible' => $this->visible
			];
		}

		return $data;
	}

}
