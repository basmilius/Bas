<?php
/**
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Request\Validate;

use Exception;

/**
 * Class RequestValidatorException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Request\Validate
 * @since 1.2.0
 */
final class RequestValidatorException extends Exception
{

	public const ERR_MISSING = 0xBFBF001;
	public const ERR_NEEDS_TO_BE_BOOLEAN = 0xBFBF002;
	public const ERR_NEEDS_TO_BE_INTEGER = 0xBFBF003;
	public const ERR_NEEDS_TO_BE_STRING = 0xBFBF004;
	public const ERR_TOO_HIGH = 0xBFBF005;
	public const ERR_TOO_LOW = 0xBFBF006;
	public const ERR_NOT_AN_URL = 0xBFBF007;
	public const ERR_NOT_A_SECURE_URL = 0xBFBF008;
	public const ERR_NOT_AN_EMAIL = 0xBFBF009;
	public const ERR_TOO_LONG = 0xBFBF010;
	public const ERR_TOO_SHORT = 0xBFBF011;

	/**
	 * RequestValidatorException constructor.
	 *
	 * @param string $message
	 * @param int    $code
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public function __construct (string $message, int $code = self::ERR_MISSING)
	{
		parent::__construct($message, $code);
	}

}
