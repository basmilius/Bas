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

namespace Columba\Router;

use Exception;
use Throwable;

/**
 * Class RouterException
 *
 * @package Columba\Router
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.0
 */
final class RouterException extends Exception
{

	public const ERR_UNKNOWN = 0xFA01;
	public const ERR_NO_ROUTE_IMPLEMENTATION = 0xFA02;
	public const ERR_REFLECTION_FAILED = 0xFA03;
	public const ERR_MIDDLEWARE_NOT_FOUND = 0xFA04;
	public const ERR_MIDDLEWARE_INVALID = 0xFA05;
	public const ERR_INVALID_RESPONSE_VALUE = 0xFA06;
	public const ERR_NULL_RENDERER = 0xFA07;
	public const ERR_RENDERER_THREW_EXCEPTION = 0xFA08;
	public const ERR_REGEX_COMPILATION_FAILED = 0xFA09;

	public const ERR_NOT_FOUND = 0x404;
	public const ERR_ROUTE_THREW_EXCEPTION = 0x500;

	/**
	 * RouterException constructor.
	 *
	 * @param string         $message
	 * @param int            $code
	 * @param Throwable|null $previous
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(string $message, int $code = self::ERR_UNKNOWN, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

}
