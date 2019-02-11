<?php
/**
 * Copyright (c) 2019 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Database\Dao;

use Columba\Database\AbstractDatabaseDriver;
use Columba\Database\Cache;
use Columba\Database\DatabaseDriver;
use Columba\Database\DatabaseException;
use Columba\Database\QueryBuilder;
use Columba\Database\Transaction;
use Columba\Pagination\Pagination;
use Columba\Util\StringUtil;
use PDO;

/**
 * Class Model
 *
 * @package Columba\Database\Dao
 * @author Bas Milius <bas@mili.us>
 * @since 1.4.0
 */
abstract class Model extends AbstractModel
{

	/**
	 * @var DatabaseDriver|null
	 */
	private static $originalDb = null;

	/**
	 * @var string[]
	 */
	private static $tables = [];

	/**
	 * @var DatabaseDriver|Transaction|AbstractDatabaseDriver|null
	 */
	protected static $db = null;

	/**
	 * @var array
	 */
	protected static $mappings = [];

	/**
	 * @var string
	 */
	public static $order = 'ASC';

	/**
	 * @var string
	 */
	public static $orderBy = '';

	/**
	 * @var string
	 */
	public static $primaryKey = 'id';

	/**
	 * @var string|null
	 */
	public static $table = null;

	/**
	 * Model constructor.
	 *
	 * @param array $data
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function __construct(array $data)
	{
		parent::__construct($data);
	}

	/**
	 * Resolves a mapping.
	 *
	 * @param string       $field
	 * @param int          $id
	 * @param Model|string $modelClass
	 * @param array|null   $data
	 *
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	private function resolveMapping(string $field, int $id, string $modelClass, array &$data = null): void
	{
		if (!class_exists($modelClass))
			throw new DatabaseException(sprintf('Could not find model %s', $modelClass ?? 'NULL'), DatabaseException::ERR_FIELD_NOT_FOUND);

		$fieldName = $field;

		if (StringUtil::endsWith($field, '_id'))
			$fieldName = substr($field, 0, -3);

		$data[$fieldName] = $modelClass::get($id);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	protected function transformData(array &$data): void
	{
		foreach (static::$mappings as $field => $modelClass)
			$this->resolveMapping($field, $data[$field] ?? 0, $modelClass, $data);

		parent::transformData($data);
	}

	/**
	 * Gets all results.
	 *
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return Model[]|mixed
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function all(int $offset = 0, int $limit = PHP_INT_MAX): array
	{
		return static::select()
			->orderBy(self::$orderBy . ' ' . self::$order)
			->limit($limit, $offset)
			->execute()
			->models();
	}

	/**
	 * Gets a single result.
	 *
	 * @param mixed $id
	 * @param int   $type
	 *
	 * @return Model|mixed|null
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function get($id, int $type = PDO::PARAM_INT): ?self
	{
		if (Cache::has($id, get_called_class()))
			return Cache::get($id, get_called_class());

		return static::where(static::table() . '.' . static::$primaryKey, '=', [$id, $type])
			->execute()
			->model();
	}

	/**
	 * Gets multiple results by id.
	 *
	 * @param array $ids
	 * @param int   $type
	 *
	 * @return Model[]|mixed
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function find($ids, int $type = PDO::PARAM_INT): array
	{
		if (Cache::hasAll($ids, get_called_class()))
			return Cache::getAll($ids, get_called_class());

		foreach ($ids as &$id)
			$id = self::$db->quote(strval($id), $type);

		return static::where(static::table() . '.' . static::$primaryKey, 'IN(' . implode(',', $ids) . ')')
			->orderBy(self::$orderBy . ' ' . self::$order)
			->execute()
			->models();
	}

	/**
	 * Paginates through results.
	 *
	 * @param int             $offset
	 * @param int             $limit
	 * @param Pagination|null $pagination
	 * @param callable|null   $conditions
	 *
	 * @return Model[]|mixed
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function paginate(int $offset = 0, int $limit = 0, ?Pagination &$pagination = null, ?callable $conditions = null): array
	{
		$queryBuilder = self::$db
			->selectFoundRows(static::table() . '.*')
			->withModel(get_called_class())
			->from(static::table());

		if ($conditions !== null)
			$conditions($queryBuilder);

		$result = $queryBuilder
			->orderBy(self::$orderBy . ' ' . self::$order)
			->limit($limit, $offset)
			->execute();

		$pagination = Pagination::simple($offset, $limit, $result->foundRows());

		return $result->models();
	}

	/**
	 * Starts a select query on the model.
	 *
	 * @param string $selectMode
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function select(string $selectMode = ''): QueryBuilder
	{
		return self::$db
			->selectCustom($selectMode, static::table() . '.*')
			->withModel(get_called_class())
			->from(static::table());
	}

	/**
	 * Updates a result.
	 *
	 * @param mixed $id
	 * @param array $fieldsAndValues
	 *
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function update($id, array $fieldsAndValues): void
	{
		$model = Cache::get($id, get_called_class());
		$queryBuilder = self::$db
			->update(static::table());

		foreach ($fieldsAndValues as $field => $value)
		{
			$realValue = is_array($value) ? $value[0] : $value;

			if ($model !== null)
				if (isset(static::$mappings[$field]))
					$model->resolveMapping($field, $realValue, static::$mappings[$field], $model->data);
				else
					$model[$field] = $realValue;

			$queryBuilder->set($field, $value);
		}

		$queryBuilder
			->where(static::table() . '.' . static::$primaryKey, '=', $id)
			->execute();
	}

	/**
	 * Starts a where query on the model.
	 *
	 * @param string $field
	 * @param string $comparator
	 * @param string $value
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function where(string $field, string $comparator = '', $value = ''): QueryBuilder
	{
		return static::select()
			->where($field, $comparator, $value);
	}

	/**
	 * Starts a transaction on all models.
	 *
	 * @return Transaction
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public static function transaction(): Transaction
	{
		if (self::$db instanceof Transaction)
			throw new DatabaseException('There is already an active transaction.', DatabaseException::ERR_TRANSACTION_FAILED);

		return self::$db = self::$originalDb->begin();
	}

	/**
	 * Commits the active transaction.
	 *
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public static function transactionCommit(): void
	{
		if (!(self::$db instanceof Transaction))
			throw new DatabaseException('There is no active transaction.', DatabaseException::ERR_TRANSACTION_FAILED);

		self::$db->commit();
		self::$db = self::$originalDb;
	}

	/**
	 * Cancels the active transaction and rolls everything back.
	 *
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public static function transactionRollBack(): void
	{
		if (!(self::$db instanceof Transaction))
			throw new DatabaseException('There is no active transaction.', DatabaseException::ERR_TRANSACTION_FAILED);

		self::$db->rollBack();
		self::$db = self::$originalDb;
	}

	/**
	 * Initializes the model.
	 *
	 * @param DatabaseDriver $db
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final static function init(DatabaseDriver $db): void
	{
		self::$db = $db;
		self::$originalDb = $db;
	}

	/**
	 * Gets the table name.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function table(): string
	{
		if (!isset(self::$tables[get_called_class()]))
		{
			self::$orderBy = self::$primaryKey;
			self::$tables[get_called_class()] = static::$table ?? StringUtil::toSnakeCase(get_called_class());
		}

		return self::$tables[get_called_class()];
	}

}
