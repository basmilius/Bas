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

use Exception;
use PDOException;

/**
 * Class DatabaseException
 *
 * @package Columba\Database
 * @author Bas Milius <bas@mili.us>
 * @since 1.0.0
 */
class DatabaseException extends Exception
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

	/**
	 * DatabaseException constructor.
	 *
	 * @param string            $message
	 * @param int               $code
	 * @param PDOException|null $previous
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct(string $message, int $code, ?PDOException $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

}
