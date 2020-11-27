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

use ArrayAccess;
use function array_reduce;
use function ceil;
use function floor;
use function max;
use function min;
use function round;

/**
 * Class MathUtil
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Util
 * @since 1.1.0
 */
final class MathUtil
{

	/**
	 * Clamps {@see $value} between {@see $min} and {@see $max}.
	 *
	 * @param float|int $value
	 * @param float|int $min
	 * @param float|int $max
	 *
	 * @return float|int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function clamp(float|int $value, float|int $min, float|int $max): float|int
	{
		return max($min, min($max, $value));
	}

	/**
	 * Rounds the value up to the nearest multiple.
	 *
	 * @param float|int $value
	 * @param int $step
	 *
	 * @return float|int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public static function ceilStep(float|int $value, int $step = 1): float|int
	{
		return ceil($value / $step) * $step;
	}

	/**
	 * Rounds the value down to the nearest multiple.
	 *
	 * @param float|int $value
	 * @param int $step
	 *
	 * @return float|int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public static function floorStep(float|int $value, int $step = 1): float|int
	{
		return floor($value / $step) * $step;
	}

	/**
	 * Rounds the value to the nearest multiple.
	 *
	 * @param float|int $value
	 * @param int $step
	 *
	 * @return float|int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public static function roundStep(float|int $value, int $step = 1): float|int
	{
		return round($value / $step) * $step;
	}

	/**
	 * Sums an array by sub-key.
	 *
	 * @param array $arr
	 * @param string $key
	 * @param float $startWith
	 *
	 * @return float
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function sumArrayKey(array $arr, string $key, float $startWith = 0): float
	{
		return array_reduce($arr, fn(float $sum, ArrayAccess $obj): float => $sum + $obj[$key], $startWith);
	}

}
