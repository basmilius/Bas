<?php
/**
 * This file is part of the Bas package.
 *
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bas\Util;

/**
 * Class ArrayUtil
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Util
 * @since 1.0.0
 */
final class ArrayUtil
{

	/**
	 * Returns TRUE if {@see $arr} is an accociative array.
	 *
	 * @param array $arr
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function isAssociativeArray (array $arr): bool
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
	public static function isSequentialArray (array $arr): bool
	{
		return count(array_filter(array_keys($arr), 'is_int')) === count($arr);
	}

}
