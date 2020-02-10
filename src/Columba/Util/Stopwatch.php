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

use function function_exists;
use function hrtime;
use function microtime;

/**
 * Class Stopwatch
 *
 * @package Columba\Util
 * @author Bas Milius <bas@mili.us>
 * @since 1.5.0
 */
final class Stopwatch
{

	public const UNIT_NANOSECONDS = 1;
	public const UNIT_MICROSECONDS = 2;
	public const UNIT_MILLISECONDS = 4;
	public const UNIT_SECONDS = 8;

	private static array $registry = [];

	/**
	 * Starts a stopwatch.
	 *
	 * @param string $id
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public static function start(string $id): void
	{
		self::$registry[$id] = self::time();
	}

	/**
	 * Stops a stopwatch and calculates the result.
	 *
	 * @param string     $id
	 * @param float|null $time
	 * @param int        $unit
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public static function stop(string $id, float &$time = null, int $unit = self::UNIT_NANOSECONDS): void
	{
		$startTime = self::$registry[$id];
		$stopTime = self::time();
		$time = $stopTime - $startTime;

		if ($unit === self::UNIT_NANOSECONDS)
			return;

		switch ($unit)
		{
			case self::UNIT_MICROSECONDS:
				$time *= 1e-3;
				break;

			case self::UNIT_MILLISECONDS:
				$time *= 1e-6;
				break;

			case self::UNIT_SECONDS:
				$time *= 1e-9;
				break;
		}
	}

	/**
	 * Gets the current high resolution time.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public static function time(): int
	{
		if (function_exists('hrtime'))
			return hrtime(true);
		else
			return (int)(microtime(true) * 1e9);
	}

}
