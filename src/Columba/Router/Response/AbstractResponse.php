<?php
/**
 * Copyright (c) 2019 - 2020 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Router\Response;

use Columba\Facade\Debuggable;
use Columba\Http\ResponseCode;
use Columba\Router\Context;
use Columba\Util\ServerTiming;
use function header;
use function http_response_code;

/**
 * Class AbstractResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router\Response
 * @since 1.3.0
 */
abstract class AbstractResponse implements Debuggable
{

	private array $headers = [];

	/**
	 * Adds a response header.
	 *
	 * @param string $name
	 * @param mixed $values
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function addHeader(string $name, string ...$values): void
	{
		$this->headers[] = [$name, $values];
	}

	/**
	 * Prints the response to the output buffer.
	 *
	 * @param Context $context
	 * @param mixed $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function print(Context $context, $value): void
	{
		$output = $this->respond($context, $value);

		$protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
		$statusCode = http_response_code() ?: ResponseCode::OK;
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
	 * @param Context $context
	 * @param mixed $value
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected abstract function respond(Context $context, $value): string;

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __debugInfo(): array
	{
		return [];
	}

}
