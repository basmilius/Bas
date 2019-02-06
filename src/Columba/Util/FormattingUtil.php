<?php
/**
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Util;

/**
 * Class FormattingUtil
 *
 * @package Columba\Util
 * @author Bas Milius <bas@mili.us>
 * @since 1.0.0
 */
final class FormattingUtil
{

	/**
	 * Formats {@see $bytes} into a string representation.
	 *
	 * @param int  $bytes
	 * @param int  $decimals
	 * @param bool $siMode
	 * @param bool $bits
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public static function formatBytes(int $bytes, int $decimals = 2, bool $siMode = true, bool $bits = false): string
	{
		$si = ['', 'k', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y'];
		$iec = ['', 'Ki', 'Mi', 'Gi', 'Ti', 'Pi', 'Ei', 'Zi', 'Yi'];

		if ($siMode)
		{
			$factor = 1024;
			$symbols = $si;
		}
		else
		{
			$factor = 1024;
			$symbols = $iec;
		}

		if ($bits)
		{
			$bytes *= 8;
		}

		for ($i = 0; $i < count($symbols) - 1 && $bytes >= $factor; $i++)
			$bytes /= $factor;

		return round($bytes, $decimals) . ' ' . $symbols[$i] . ($bits ? 'b' : 'B');
	}

	/**
	 * Formats {@see $bytes} into a string representation with /s suffix.
	 *
	 * @param int  $bytes
	 * @param int  $decimals
	 * @param bool $siMode
	 * @param bool $bits
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public static function formatBytesPerSecond(int $bytes, int $decimals = 2, bool $siMode = true, bool $bits = false): string
	{
		return self::formatBytes($bytes, $decimals, $siMode, $bits) . '/s';
	}

	/**
	 * Converts minutes to HH:mm.
	 *
	 * @param int $minutes
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function formatHoursMinutesFromMinutes(int $minutes): string
	{
		if ($minutes === 0)
			return '00:00';

		$wasNegative = $minutes < 0;
		$minutes = abs($minutes);

		$hours = floor($minutes / 60);
		$minutes = $minutes % 60;

		$format = ($hours < 10 ? '0' . $hours : $hours) . ':' . ($minutes < 10 ? '0' . $minutes : $minutes);

		return ($wasNegative ? '-' : '') . $format;
	}

}
