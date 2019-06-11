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

namespace Columba\Database;

use Columba\Error\ColumbaException;

/**
 * Class DatabaseException
 *
 * @package Columba\Database
 * @author Bas Milius <bas@mili.us>
 * @since 1.0.0
 */
class DatabaseException extends ColumbaException
{

	public const ERR_CONNECTION_FAILED = 1;
	public const ERR_CLASS_NOT_FOUND = 2;
	public const ERR_FIELD_NOT_FOUND = 4;
	public const ERR_MODEL_NOT_FOUND = 8;
	public const ERR_QUERY_FAILED = 16;
	public const ERR_TRANSACTION_FAILED = 32;
	public const ERR_FILE_NOT_READABLE = 64;
	public const ERR_FEATURE_UNSUPPORTED = 128;
	public const ERR_UNSUPPORTED = 256;
	public const ERR_PROCEDURE_FAILED = 512;
	public const ERR_INVALID_OFFSET = 1024;
	public const ERR_IMMUTABLE = 2048;
	public const ERR_NO_RESULTS = 4096;

}
