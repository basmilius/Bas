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

namespace Columba\Router\Response;

use Columba\Http\ResponseCode;
use Columba\Router\RouteContext;
use Columba\Util\ServerTiming;

/**
 * Class AbstractResponse
 *
 * @package Columba\Router\Response
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.0
 */
abstract class AbstractResponse
{

	/**
	 * @var string
	 */
	private $body;

	/**
	 * @var int
	 */
	private $code;

	/**
	 * @var array
	 */
	private $headers;

	/**
	 * AbstractResponse constructor.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct()
	{
		$this->body = '';
		$this->code = 200;
		$this->headers = [];
	}

	/**
	 * Prints the response to the output buffer.
	 *
	 * @param RouteContext $context
	 * @param mixed        $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function print(RouteContext $context, $value): void
	{
		$output = $this->respond($context, $value);

		$protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
		$statusCode = http_response_code() ?: 200;
		$statusMessage = ResponseCode::getMessage($statusCode);

		header("$protocol $statusCode $statusMessage");
		ServerTiming::appendHeader();

		foreach ($this->headers as [$name, $values])
			foreach ($values as $headerValue)
				header($name . ': ' . $headerValue);

		echo $output;
	}

	/**
	 * Respond to the webbrowser.
	 *
	 * @param RouteContext $context
	 * @param mixed        $value
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected abstract function respond(RouteContext $context, $value): string;

	/**
	 * Adds a response header.
	 *
	 * @param string $name
	 * @param string ...$values
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected final function addHeader(string $name, string ...$values): void
	{
		$this->headers[] = [$name, $values];
	}

}
