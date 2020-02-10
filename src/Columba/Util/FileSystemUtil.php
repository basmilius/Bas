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

use function array_shift;
use function count;
use function realpath;
use function round;
use function scandir;

/**
 * Class FileSystemUtil
 *
 * @package Columba\Util
 * @author Bas Milius <bas@mili.us>
 * @since 1.4.0
 */
final class FileSystemUtil
{

	/**
	 * Formats bytes into a string representation.
	 *
	 * @param int  $value
	 * @param int  $decimals
	 * @param bool $siMode
	 * @param bool $bits
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function formatBytes(int $value, int $decimals = 2, bool $siMode = true, bool $bits = false): string
	{
		$si = ['', 'k', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y'];
		$iec = ['', 'Ki', 'Mi', 'Gi', 'Ti', 'Pi', 'Ei', 'Zi', 'Yi'];
		$factor = 1024;
		$symbols = $siMode ? $si : $iec;

		if ($bits)
			$value *= 8;

		for ($i = 0, $symbolsCount = count($symbols); $i < $symbolsCount - 1 && $value >= $factor; ++$i)
			$value /= $factor;

		return round($value, $decimals) . ' ' . $symbols[$i] . ($bits ? 'b' : 'B');
	}

	/**
	 * Wrapper for scandir that removes dot-folders.
	 *
	 * @param string $directory
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function scanDir(string $directory): array
	{
		$entries = scandir($directory);

		array_shift($entries);
		array_shift($entries);

		foreach ($entries as &$entry)
			$entry = realpath($directory . DIRECTORY_SEPARATOR . $entry);

		return $entries;
	}

}
