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

use function count;
use function header;
use function implode;

/**
 * Class ServerTiming
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Util
 * @since 1.5.0
 */
class ServerTiming
{

	private static array $timings = [];

	/**
	 * Appends the Server-Timing header.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public static function appendHeader(): void
	{
		if (count(static::$timings) === 0)
			return;

		$segments = [];

		foreach (static::$timings as [$description, $type, $time])
			$segments[] = $type . ';desc="' . $description . '";dur=' . $time;

		header('Server-Timing: ' . implode(',', $segments));
	}

	/**
	 * Start a timer.
	 *
	 * @param string $id
	 * @param string $description
	 * @param string $type
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 *
	 * @see Stopwatch::start()
	 */
	public static function start(string $id, string $description = '', string $type = 'cpu'): void
	{
		Stopwatch::start($id);

		static::$timings[$id] = [$description, $type, null];
	}

	/**
	 * Stops a timer.
	 *
	 * @param string   $id
	 * @param int|null $time
	 * @param int      $unit
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 *
	 * @see Stopwatch::stop()
	 */
	public static function stop(string $id, ?int &$time = null, int $unit = Stopwatch::UNIT_NANOSECONDS): void
	{
		if (!isset(static::$timings[$id]))
			return;

		Stopwatch::stop($id, $time, Stopwatch::UNIT_NANOSECONDS);

		static::$timings[$id][2] = $time * 1e-6;

		switch ($unit)
		{
			case Stopwatch::UNIT_MICROSECONDS:
				$time *= 1e-3;
				break;

			case Stopwatch::UNIT_MILLISECONDS:
				$time *= 1e-6;
				break;

			case Stopwatch::UNIT_SECONDS:
				$time *= 1e-9;
				break;
		}
	}

}
