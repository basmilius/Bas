<?php
declare(strict_types=1);

namespace Bas\Util;

/**
 * Class ExecutionTime
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Util
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
