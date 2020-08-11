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

namespace Columba\Util;

use function array_filter;
use function array_flip;
use function array_intersect_key;
use function array_key_first;
use function array_keys;
use function array_reverse;
use function array_values;
use function count;
use function is_null;

/**
 * Class ArrayUtil
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Util
 * @since 1.0.0
 */
final class ArrayUtil
{

	/**
	 * Returns the first element of the given array. When {@see $fn} is given, it's used as a truth check.
	 *
	 * @param array $arr
	 * @param callable|null $fn
	 * @param mixed $default
	 *
	 * @return mixed|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function first(array $arr, callable $fn = null, $default = null)
	{
		if (is_null($fn))
		{
			if (empty($arr))
				return $default;

			$key = array_key_first($arr);

			if ($key === null)
				return $default;

			return $arr[$key];
		}

		foreach ($arr as $key => $value)
			if ($fn($value, $key))
				return $value;

		return $default;
	}

	/**
	 * Returns the last element of the given array. When {@see $fn} is given, it's used as a truth check.
	 *
	 * @param array $arr
	 * @param callable|null $fn
	 * @param mixed $default
	 *
	 * @return mixed|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function last(array $arr, callable $fn = null, $default = null)
	{
		$arr = array_reverse($arr);

		return self::first($arr, $fn, $default);
	}

	/**
	 * Groups a multidimensional array by key.
	 *
	 * @param array $arr
	 * @param mixed $key
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public static function groupBy(array $arr, $key): array
	{
		$result = [];

		foreach ($arr as $item)
			$result[$item[$key]][] = $item;

		return array_values($result);
	}

	/**
	 * Returns a subset of the given array.
	 *
	 * @param array $arr
	 * @param array $keys
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function only(array $arr, array $keys): array
	{
		return array_intersect_key($arr, array_flip($keys));
	}

	/**
	 * Returns TRUE if {@see $arr} is an accociative array.
	 *
	 * @param array $arr
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function isAssociativeArray(array $arr): bool
	{
		return count(array_filter(array_keys($arr), 'is_string')) === count($arr);
	}

	/**
	 * Returns TRUE if {@see $arr} is an sequential array.
	 *
	 * @param array $arr
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function isSequentialArray(array $arr): bool
	{
		return count(array_filter(array_keys($arr), 'is_int')) === count($arr);
	}

}
