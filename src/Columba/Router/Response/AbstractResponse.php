<?php
declare(strict_types=1);

namespace Columba\Router\Response;

use Columba\Router\RouterException;

/**
 * Class AbstractResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router\Response
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
	 * @param $value
	 *
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function print($value): void
	{
		$output = $this->respond($value);

		foreach ($this->headers as [$name, $values])
			foreach ($values as $headerValue)
				header($name . ': ' . $headerValue);

		header('Content-Length: ' . mb_strlen($output));

		echo $output;
	}

	/**
	 * Respond to the webbrowser.
	 *
	 * @param mixed $value
	 *
	 * @return string
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected abstract function respond($value): string;

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

	/**
	 * Gets the HTTP Response Code.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected final function getResponseCode(): int
	{
		return http_response_code();
	}

	/**
	 * Sets the HTTP Response Code.
	 *
	 * @param int $responseCode
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected final function setHttpResponseCode(int $responseCode): void
	{
		http_response_code($responseCode);
	}

}
