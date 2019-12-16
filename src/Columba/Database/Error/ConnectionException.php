<?php
/**
 * Copyright (c) 2017 - 2019 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Latte Framework package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Database\Error;

/**
 * Class ConnectionException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Error
 * @since 1.6.0
 */
final class ConnectionException extends DatabaseException
{

	public const ERR_INCOMPLETE_OPTIONS = 1;
	public const ERR_UNDEFINED_CONNECTION = 2;
	public const ERR_INVALID_CONNECTION = 4;

	public const ERR_ACCESS_DENIED = 1045;
	public const ERR_ACCESS_DENIED_PASSWORD = 1698;

}
