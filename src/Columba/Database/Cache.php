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

namespace Columba\Database;

use Columba\Database\Dao\Model;

/**
 * Class Cache
 *
 * @package Columba\Database
 * @author Bas Milius <bas@mili.us>
 * @since 1.4.0
 */
final class Cache
{

	/**
	 * @var array
	 */
	private static $cache = [];

	/**
	 * Gets a cached result.
	 *
	 * @param mixed  $id
	 * @param string $modelClass
	 *
	 * @return Model|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function get($id, string $modelClass): ?Model
	{
		return self::$cache[$modelClass][$id] ?? null;
	}

	/**
	 * Returns all cached results matching ids.
	 *
	 * @param array  $ids
	 * @param string $modelClass
	 *
	 * @return Model[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function getAll(array $ids, string $modelClass): array
	{
		$results = [];

		foreach ($ids as $id)
			$results[] = self::get($id, $modelClass);

		return $results;
	}

	/**
	 * Returns TRUE if a model is cached.
	 *
	 * @param mixed  $id
	 * @param string $modelClass
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function has($id, string $modelClass): bool
	{
		return isset(self::$cache[$modelClass][$id]);
	}

	/**
	 * Returns TRUE if all results are cached.
	 *
	 * @param array  $ids
	 * @param string $modelClass
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function hasAll(array $ids, string $modelClass): bool
	{
		foreach ($ids as $id)
			if (!self::has($id, $modelClass))
				return false;

		return true;
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
		self::$cache[get_class($model)][$model[$model::$primaryKey]] = $model;
	}

}
