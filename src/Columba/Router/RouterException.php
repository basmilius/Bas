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

use Columba\Error\ColumbaException;

/**
 * Class RouterException
 *
 * @package Columba\Router
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.0
 */
final class RouterException extends ColumbaException
{

	public const ERR_UNKNOWN = 1;
	public const ERR_NO_ROUTE_IMPLEMENTATION = 2;
	public const ERR_REFLECTION_FAILED = 4;
	public const ERR_MIDDLEWARE_NOT_FOUND = 8;
	public const ERR_MIDDLEWARE_INVALID = 16;
	public const ERR_INVALID_RESPONSE_VALUE = 32;
	public const ERR_NULL_RENDERER = 64;
	public const ERR_RENDERER_THREW_EXCEPTION = 128;
	public const ERR_REGEX_COMPILATION_FAILED = 256;
	public const ERR_NOT_FOUND = 512;
	public const ERR_ROUTE_THREW_EXCEPTION = 1024;
	public const ERR_ILLEGAL = 2048;

}
