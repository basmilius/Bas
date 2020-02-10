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

namespace Columba\Foundation\Http\RateLimit\Storage;

/**
 * Interface IStorageAdapter
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Foundation\Http\RateLimit\Storage
 * @since 1.6.0
 */
interface IStorageAdapter
{

	/**
	 * Returns TRUE if a key exists.
	 *
	 * @param string $key
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function exists(string $key): bool;

	/**
	 * Gets the amount of requests a key has made.
	 *
	 * @param string $key
	 *
	 * @return float
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function get(string $key): float;

	/**
	 * Removes a key.
	 *
	 * @param string $key
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function remove(string $key): void;

	/**
	 * Sets a key.
	 *
	 * @param string $key
	 * @param float  $value
	 * @param int    $ttl
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function set(string $key, float $value, int $ttl): void;

}
