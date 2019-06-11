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

namespace Columba\Request\Validate;

use Columba\Error\ColumbaException;

/**
 * Class RequestValidatorException
 *
 * @package Columba\Request\Validate
 * @author Bas Milius <bas@mili.us>
 * @since 1.2.0
 */
final class RequestValidatorException extends ColumbaException
{

	public const ERR_MISSING = 1;
	public const ERR_NEEDS_TO_BE_BOOLEAN = 2;
	public const ERR_NEEDS_TO_BE_FLOAT = 4;
	public const ERR_NEEDS_TO_BE_INTEGER = 8;
	public const ERR_NEEDS_TO_BE_STRING = 16;
	public const ERR_TOO_HIGH = 32;
	public const ERR_TOO_LOW = 64;
	public const ERR_NOT_AN_URL = 128;
	public const ERR_NOT_A_SECURE_URL = 256;
	public const ERR_NOT_AN_EMAIL = 512;
	public const ERR_TOO_LONG = 1024;
	public const ERR_TOO_SHORT = 2048;
	public const ERR_DIDNT_MATCH = 4096;

}
