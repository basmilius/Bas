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

	/**
	 * @var array
	 */
	private static $registry = [];

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
		self::$registry[$id] = hrtime(true);
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
		$time = 0.0;

		$stopTime = hrtime(true);
		$diffTime = $stopTime - $startTime;

		switch ($unit)
		{
			case self::UNIT_NANOSECONDS:
				$time = $diffTime;
				break;

			case self::UNIT_MICROSECONDS:
				$time = $diffTime / 1000;
				break;

			case self::UNIT_MILLISECONDS:
				$time = $diffTime / 1000000;
				break;

			case self::UNIT_SECONDS:
				$time = $diffTime / 1000000000;
				break;
		}
	}

}
