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

namespace Columba\Database\Error;

/**
 * Class SchemaException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Error
 * @since 1.6.0
 */
final class SchemaException extends DatabaseException
{

	public const ERR_NO_SUCH_COLUMN = 1054;
	public const ERR_NO_SUCH_TABLE = 1146;

}
