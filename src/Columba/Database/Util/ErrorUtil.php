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

namespace Columba\Database\Util;

use Columba\Database\Error\ConnectionException;
use Columba\Database\Error\DatabaseException;
use Columba\Database\Error\SchemaException;
use Throwable;

/**
 * Class ErrorUtil
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Util
 * @since 1.6.0
 */
final class ErrorUtil
{

	/**
	 * Throws one of the {@see DatabaseException} implementations based on the given code.
	 *
	 * @param int            $code
	 * @param string         $message
	 * @param Throwable|null $err
	 *
	 * @return DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function throw(int $code, string $message, ?Throwable $err = null): DatabaseException
	{
		switch ($code)
		{
			case ConnectionException::ERR_ACCESS_DENIED:
			case ConnectionException::ERR_ACCESS_DENIED_PASSWORD:
				return new ConnectionException($message, $code);

			case SchemaException::ERR_NO_SUCH_COLUMN:
			case SchemaException::ERR_NO_SUCH_TABLE:
				return new SchemaException($message, $code, $err);

			default:
				return new DatabaseException($message, $code, $err);
		}
	}

}
