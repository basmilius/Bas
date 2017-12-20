<?php
declare(strict_types=1);

namespace Bas\Util;

/**
 * Class FormattingUtil
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Util
 * @since 1.0.0
 */
final class FormattingUtil
{

	public static function formatHoursMinutesFromMinutes (int $minutes): string
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
