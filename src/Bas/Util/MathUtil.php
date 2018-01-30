<?php
/**
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Bas package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bas\Util;

/**
 * Class MathUtil
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Util
 * @since 1.1.0
 */
final class MathUtil
{

	/**
	 * Clamps {@see $value} between {@see $min} and {@see $max}.
	 *
	 * @param int $value
	 * @param int $min
	 * @param int $max
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function clamp (int $value, int $min, int $max): int
	{
		return max($min, min($max, $value));
	}

}
