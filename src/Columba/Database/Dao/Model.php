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
 * @package Columba\Database\Dao
 * @since 1.4.0
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
	 * @var string[]
	 */
	private static $tables = [];

	/**
	 * Model constructor.
	 *
	 * @param array $data
	 *
	 * @since 1.4.0
	 * @author Bas Milius <bas@mili.us>
	 */
	public function __construct(array $data)
	{
		parent::__construct($data);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	protected function transformData(array &$data): void
	{
		foreach (static::$mappings as $field => $modelClass)
		{
			if (!class_exists($modelClass))
				throw new DatabaseException(sprintf('Could not find model %s', $modelClass ?? 'NULL'), DatabaseException::ERR_FIELD_NOT_FOUND);

			$fieldName = $field;

			if (StringUtil::endsWith($field, '_id'))
				$fieldName = substr($field, 0, -3);

			$data[$fieldName] = $modelClass::get($data[$field] ?? 0);
		}

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
	 * @since
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
	 * @since
	 */
	public static function paginate(int $offset = 0, int $limit = 0, ?Pagination &$pagination = null, ?callable $conditions = null): array
	{
		$queryBuilder = static::$db
			->selectFoundRows(static::table() . '.*')
			->withModel(get_called_class())
			->from(static::table());

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
		return static::$db
			->select(self::table() . '.*')
			->withModel(get_called_class())
			->from(self::table());
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
			self::$tables[get_called_class()] = StringUtil::toSnakeCase(get_called_class());

		return self::$tables[get_called_class()];
	}

}
