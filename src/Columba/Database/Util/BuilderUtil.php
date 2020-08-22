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

use function max;
use function str_repeat;

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
		return str_repeat($str, max(0, $amount)) ?? '';
	}

}
