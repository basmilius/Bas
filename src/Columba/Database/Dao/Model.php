<?php
declare(strict_types=1);

namespace Columba\Database\Dao;

use Columba\Database\AbstractDatabaseDriver;
use Columba\Database\Cache;
use Columba\Database\DatabaseException;
use Columba\Database\QueryBuilder;
use Columba\Pagination\Pagination;
use Columba\Util\StringUtil;

/**
 * Class Model
 *
 * @author Bas Milius <bas@mili.us>
 * @since 1.4.0
 * @package Columba\Database\Dao
 */
abstract class Model extends AbstractModel
{

	/**
	 * @var AbstractDatabaseDriver|null
	 */
	protected static $db = null;

	/**
	 * @var array
	 */
	protected static $mappings = [];

	/**
	 * @var string|null
	 */
	protected static $table = null;

	/**
	 * @var string[]
	 */
	private static $tables = [];

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
	 * @return Model[]
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function all(int $offset = 0, int $limit = PHP_INT_MAX): array
	{
		return self::select()
			->limit($limit, $offset)
			->execute()
			->models();
	}

	/**
	 * Gets a single result.
	 *
	 * @param int $id
	 *
	 * @return Model|null
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function get(int $id): ?self
	{
		if (Cache::has($id, get_called_class()))
			return Cache::get($id, get_called_class());

		return self::where('id', '=', $id)
			->execute()
			->model();
	}

	/**
	 * Gets multiple results by id.
	 *
	 * @param int ...$ids
	 *
	 * @return Model[]
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function find(int ...$ids): array
	{
		if (Cache::hasAll($ids, get_called_class()))
			return Cache::getAll($ids, get_called_class());

		return self::where('id', 'IN(' . implode(',', $ids) . ')')
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
	 * @return array
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function paginate(int $offset = 0, int $limit = 0, ?Pagination &$pagination = null, ?callable $conditions = null): array
	{
		$queryBuilder = self::$db
			->selectFoundRows(self::table() . '.*')
			->withModel(get_called_class())
			->from(self::table());

		if ($conditions !== null)
			$conditions($queryBuilder);

		$result = $queryBuilder
			->limit($limit, $offset)
			->execute();

		$pagination = Pagination::simple($offset, $limit, $result->foundRows());

		return $result->models();
	}

	/**
	 * Starts a select query on the model.
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function select(): QueryBuilder
	{
		return self::$db
			->select(self::table() . '.*')
			->withModel(get_called_class())
			->from(self::table());
	}

	/**
	 * Updates a result.
	 *
	 * @param int   $id
	 * @param array $fieldsAndValues
	 *
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function update(int $id, array $fieldsAndValues): void
	{
		$model = Cache::get($id, get_called_class());
		$queryBuilder = self::$db
			->update(self::table());

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
			->where('id', '=', $id)
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
		return self::select()
			->where($field, $comparator, $value);
	}

	/**
	 * Initializes the model.
	 *
	 * @param AbstractDatabaseDriver $db
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final static function init(AbstractDatabaseDriver $db): void
	{
		self::$db = $db;
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
			self::$tables[get_called_class()] = static::$table ?? StringUtil::toSnakeCase(get_called_class());

		return self::$tables[get_called_class()];
	}

}
