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

	public const ERR_CONNECTION_FAILED = 0xDBA003;
	public const ERR_CLASS_NOT_FOUND = 0xDBA019;
	public const ERR_FIELD_NOT_FOUND = 0xDBA021;
	public const ERR_MODEL_NOT_FOUND = 0xDBA030;
	public const ERR_QUERY_FAILED = 0xDBA0439;

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
