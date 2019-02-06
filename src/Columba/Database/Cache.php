<?php
declare(strict_types=1);

namespace Columba\Database;

use Columba\Database\Dao\Model;

/**
 * Class Cache
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database
 * @since 1.4.0
 */
final class Cache
{

	/**
	 * @var array
	 */
	private static $cache = [];

	/**
	 * Gets a cached model.
	 *
	 * @param int    $id
	 * @param string $modelClass
	 *
	 * @return Model|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function get(int $id, string $modelClass): ?Model
	{
		return self::$cache[$modelClass][$id] ?? null;
	}

	/**
	 * Returns true if a model is cached.
	 *
	 * @param int    $id
	 * @param string $modelClass
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function has(int $id, string $modelClass): bool
	{
		return isset(self::$cache[$modelClass][$id]);
	}

	/**
	 * Adds a model to cache.
	 *
	 * @param Model $model
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function set(Model $model): void
	{
		self::$cache[get_class($model)][$model['id']] = $model;
	}

}
