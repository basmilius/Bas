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
 * Class FormattingUtil
 *
 * @package Columba\Util
 * @author Bas Milius <bas@mili.us>
 * @since 1.0.0
 */
final class FormattingUtil
{

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
