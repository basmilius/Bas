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

namespace Columba\Database\Util;

use Columba\Util\ArrayUtil;
use function explode;
use function max;
use function str_repeat;
use function trim;

/**
 * Class BuilderUtil
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Util
 * @since 1.6.0
 */
final class BuilderUtil
{

	/**
	 * Repeats the given string for the given amount.
	 *
	 * @param string $str
	 * @param int $amount
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function repeat(string $str, int $amount): string
	{
		if ($amount === 0)
			return '';

		return str_repeat($str, max(0, $amount)) ?? '';
	}

	/**
	 * Trims the given key and returns only the column part.
	 *
	 * @param string $key
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function trimKey(string $key): string
	{
		$parts = explode('.', $key);
		$part = ArrayUtil::last($parts);

		return trim($part, '`');
	}

}
