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

namespace Columba\Security\JWT;

use Exception;
use Throwable;

/**
 * Class JWTException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Security\JWT
 * @since 1.5.0
 */
final class JWTException extends Exception
{

	public const ERR_UNKNOWN = 0;
	public const ERR_JSON_ERROR = 1;
	public const ERR_NULL_RESULT = 2;
	public const ERR_NOT_SUPPORTED = 4;
	public const ERR_OPENSSL = 8;
	public const ERR_INVALID_ARGUMENT = 16;
	public const ERR_UNEXPECTED_ARGUMENT = 32;
	public const ERR_INVALID_SIGNATURE = 64;
	public const ERR_NOT_YET_VALID = 128;
	public const ERR_EXPIRED = 256;

	/**
	 * JWTException constructor.
	 *
	 * @param string         $message
	 * @param int            $code
	 * @param Throwable|null $previous
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function __construct(string $message, int $code = self::ERR_UNKNOWN, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

}
