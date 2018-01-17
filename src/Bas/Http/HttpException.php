<?php
declare(strict_types=1);

namespace Bas\Http;

use Exception;

/**
 * Class HttpException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Http
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
	public function __construct (string $message, int $code)
	{
		parent::__construct($message, $code);
	}

}
