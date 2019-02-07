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

namespace Columba\Util;

/**
 * Class ArrayUtil
 *
 * @package Columba\Util
 * @author Bas Milius <bas@mili.us>
 * @since 1.0.0
 */
final class ArrayUtil
{

	/**
	 * Returns the first element of an array passing the truth check.
	 *
	 * @param array         $array
	 * @param callable|null $fn
	 * @param mixed         $default
	 *
	 * @return mixed|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function first(array $array, callable $fn = null, $default = null)
	{
		if (is_null($fn))
		{
			if (empty($array))
				return $default;

			foreach ($array as $item)
				return $item;
		}

		foreach ($array as $key => $value)
			if ($fn($value, $key))
				return $value;

		return $default;
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
