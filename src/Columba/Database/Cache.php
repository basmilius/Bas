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

namespace Columba\Database;

use Columba\Database\Model\Model;
use function array_filter;
use function get_class;

/**
 * Class Cache
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database
 * @since 1.6.0
 */
class Cache
{

	private array $cache = [];

	/**
	 * Gets a cached result.
	 *
	 * @param mixed $primaryKey
	 * @param string $modelClass
	 *
	 * @return Model|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function get($primaryKey, string $modelClass): ?Model
	{
		return $this->cache[$modelClass][$primaryKey] ?? null;
	}

	/**
	 * Returns all cached results matching ids.
	 *
	 * @param array $primaryKeys
	 * @param string $modelClass
	 *
	 * @return Model[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getAll(array $primaryKeys, string $modelClass): array
	{
		$results = [];

		foreach ($primaryKeys as $primaryKey)
			$results[] = self::get($primaryKey, $modelClass);

		return array_filter($results, fn($value) => $value !== null);
	}

	/**
	 * Returns TRUE if a model is cached.
	 *
	 * @param mixed $primaryKey
	 * @param string $modelClass
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function has($primaryKey, string $modelClass): bool
	{
		return isset($this->cache[$modelClass][$primaryKey]);
	}

	/**
	 * Returns all the keys for the given model.
	 *
	 * @param string $modelClass
	 *
	 * @return string[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function keys(string $modelClass): array
	{
		return \array_keys($this->cache[$modelClass] ?? []);
	}

	/**
	 * Purges the cache.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function purge(): void
	{
		$this->cache = [];
	}

	/**
	 * Removes a model from cache.
	 *
	 * @param mixed $primaryKey
	 * @param string $modelClass
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function remove($primaryKey, string $modelClass): void
	{
		unset($this->cache[$modelClass][$primaryKey]);
	}

	/**
	 * Adds a model to cache.
	 *
	 * @param mixed $primaryKey
	 * @param Model $model
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function set($primaryKey, Model $model): void
	{
		$this->cache[get_class($model)][$primaryKey] = $model;
	}

}
