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

namespace Columba\Error;

use Columba\Http\ResponseCode;
use Columba\Util\ExceptionUtil;
use Throwable;

/**
 * Class ExceptionHandler
 *
 * @package Columba\Error
 * @author Bas Milius <bas@mili.us>
 * @since 1.4.0
 */
class ExceptionHandler
{

	/**
	 * Handles the exception.
	 *
	 * @param Throwable $err
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 * @internal
	 */
	public function onException(Throwable $err): void
	{
		if (!headers_sent())
		{
			http_response_code(ResponseCode::INTERNAL_SERVER_ERROR);
			header('Content-Type: text/html');
		}

		echo '<pre>';
		echo '<strong style="color:#AA0000; font-size: 1.5rem">Uncaught error</strong>' . PHP_EOL;

		$exceptions = ExceptionUtil::exceptionsToIterator($err);

		foreach ($exceptions as $err)
		{
			echo PHP_EOL;
			echo PHP_EOL;
			echo PHP_EOL;
			echo PHP_EOL;

			echo sprintf('<strong>%s (%s)</strong>', $err->getType(), $err->getCodeConstant()) . PHP_EOL;
			echo $err->getMessage() . PHP_EOL;

			foreach ($err->getTrace() as $trace)
			{
				echo PHP_EOL;
				echo $trace->getMethod() . PHP_EOL;
				echo sprintf('%s on line %d', $trace->getFile(), $trace->getLine());
				echo PHP_EOL;
			}
		}

		echo '</pre>';
	}

	/**
	 * Registers the exception handler.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function register(): void
	{
		set_exception_handler([new self(), 'onException']);
	}

}
