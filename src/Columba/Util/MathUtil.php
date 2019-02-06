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

use ArrayAccess;
use Columba\Database\AbstractDatabaseDriver;
use Columba\Database\DatabaseException;

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
	 * @param int $value
	 * @param int $min
	 * @param int $max
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function clamp(int $value, int $min, int $max): int
	{
		return max($min, min($max, $value));
	}

	/**
	 * Generates an unique id based on {@see $seed} and {@see $id}.
	 *
	 * @param AbstractDatabaseDriver $db
	 * @param int                    $seed
	 * @param int                    $id
	 *
	 * @return string|null
	 * @throws DatabaseException
	 * @since 1.4.0
	 * @author Bas Milius <bas@mili.us>
	 */
	public static function generateUniqueId(AbstractDatabaseDriver $db, int $seed, int $id): ?string
	{
		return $db
			->prepare('SELECT CONCAT(
			              SUBSTRING(\'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-\', FLOOR(RAND(@seed:=ROUND(rand(' . ($seed + $id) . ')*4294967296))*64+1), 1),
			              SUBSTRING(\'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-\', FLOOR(RAND(@seed:=ROUND(rand(@seed)*4294967296))*64+1), 1),
			              SUBSTRING(\'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-\', FLOOR(RAND(@seed:=ROUND(rand(@seed)*4294967296))*64+1), 1),
			              SUBSTRING(\'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-\', FLOOR(RAND(@seed:=ROUND(rand(@seed)*4294967296))*64+1), 1),
			              SUBSTRING(\'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-\', FLOOR(RAND(@seed:=ROUND(rand(@seed)*4294967296))*64+1), 1),
			              SUBSTRING(\'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-\', FLOOR(RAND(@seed)*64+1), 1)
			          ) AS unique_id')
			->execute()
			->toSingle('unique_id');
	}

	/**
	 * Sums an array by sub-key.
	 *
	 * @param array  $arr
	 * @param string $key
	 * @param float  $startWith
	 *
	 * @return float
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function sumArrayKey(array $arr, string $key, float $startWith = 0): float
	{
		return array_reduce($arr, function (float $sum, ArrayAccess $obj) use ($key): float
		{
			return $sum + $obj[$key];
		}, $startWith);
	}

}
