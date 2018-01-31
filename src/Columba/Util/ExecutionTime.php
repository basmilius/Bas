<?php
/**
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Util;

/**
 * Class ExecutionTime
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Util
 * @since 3.0.0
 */
final class ExecutionTime
{

	/**
	 * @var array
	 */
	private static $registry = [];

	/**
	 * Starts the execution timer.
	 *
	 * @param string $id
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 3.0.0
	 */
	public static function start (string $id): void
	{
		self::$registry[$id] = microtime(true);
	}

	/**
	 * Stops the execution timer.
	 *
	 * @param string $id
	 *
	 * @return float
	 * @author Bas Milius <bas@mili.us>
	 * @since 3.0.0
	 */
	public static function stop (string $id): float
	{
		$startTime = self::$registry[$id];

		if ($startTime === null)
			return 0.0;

		$stopTime = microtime(true);
		$diffTime = $stopTime - $startTime;

		return $diffTime;
	}

}
