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

namespace Columba\Http;

use Exception;

/**
 * Class HttpException
 *
 * @package Columba\Http
 * @author Bas Milius <bas@mili.us>
 * @since 1.2.0
 */
final class HttpException extends Exception
{

	public const ERR_HOST_UNRESOLVABLE = 6;

	/**
	 * HttpException constructor.
	 *
	 * @param string $message
	 * @param int    $code
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public function __construct(string $message, int $code)
	{
		parent::__construct($message, $code);
	}

}
