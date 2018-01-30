<?php
/**
 * This file is part of the Bas package.
 *
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bas\Router;

use Throwable;

/**
 * Class RouteExecutionException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Router
 * @since 1.0.0
 */
final class RouteExecutionException extends \Exception
{

	public const ERR_UNKNOWN = 0xFACC000;
	public const ERR_ROUTE_THREW_EXCEPTION = 0xFACC001;
	public const ERR_INACCESSIBLE = 0xFACC002;
	public const ERR_MISSING_PARAMETERS = 0xFACC003;
	public const ERR_SUBROUTE_NOT_FOUND = 0xFACC004;

	/**
	 * RouteExecutionException constructor.
	 *
	 * @param string         $message
	 * @param int            $code
	 * @param Throwable|null $previous
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct (string $message, int $code = self::ERR_UNKNOWN, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

}
