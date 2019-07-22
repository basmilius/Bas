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

use Columba\Columba;

/**
 * Class Request
 *
 * @package Columba\Http
 * @author Bas Milius <bas@mili.us>
 * @since 1.2.0
 */
final class Request
{

	/**
	 * @var string|null
	 */
	protected $body;

	/**
	 * @var string[]
	 */
	protected $headers;

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @var string|null
	 */
	protected $requestMethod;

	/**
	 * @var string|null
	 */
	protected $requestUrl;

	/**
	 * @var string
	 */
	protected $userAgent;

	/**
	 * Request constructor.
	 *
	 * @param string|null $requestUrl
	 * @param string|null $requestMethod
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public function __construct(?string $requestUrl = null, ?string $requestMethod = null)
	{
		$this->body = null;
		$this->headers = [];
		$this->options = [];
		$this->userAgent = 'ColumbaHttpClient/' . Columba::VERSION . ' PHP/' . phpversion();

		$this->requestMethod = $requestMethod;
		$this->requestUrl = $requestUrl;
	}

	/**
	 * Gets the body.
	 *
	 * @return string|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getBody(): ?string
	{
		return $this->body;
	}

	/**
	 * Sets the body.
	 *
	 * @param string $body
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function setBody(string $body): void
	{
		$this->body = $body;
	}

	/**
	 * Adds a HTTP header.
	 *
	 * @param string $name
	 * @param string $content
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function addHeader(string $name, string $content): void
	{
		$this->headers[$name] = $content;
	}

	/**
	 * Gets the headers.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getHeaders(): array
	{
		return $this->headers;
	}

	/**
	 * Sets the headers.
	 *
	 * @param array $headers
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function setHeaders(array $headers): void
	{
		$this->headers = $headers;
	}

	/**
	 * Gets options.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getOptions(): array
	{
		return $this->options;
	}

	/**
	 * Sets an option.
	 *
	 * @param int   $option
	 * @param mixed $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function setOption(int $option, $value): void
	{
		$this->options[$option] = $value;
	}

	/**
	 * Sets options.
	 *
	 * @param array $options
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function setOptions(array $options): void
	{
		$this->options = $options;
	}

	/**
	 * Gets the request method.
	 *
	 * @return string|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getRequestMethod(): ?string
	{
		return $this->requestMethod;
	}

	/**
	 * Sets the request method.
	 *
	 * @param string $requestMethod
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function setRequestMethod(string $requestMethod): void
	{
		$this->requestMethod = $requestMethod;
	}

	/**
	 * Gets the request url.
	 *
	 * @return string|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getRequestUrl(): ?string
	{
		return $this->requestUrl;
	}

	/**
	 * Sets the request url.
	 *
	 * @param string $requestUrl
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function setRequestUrl(string $requestUrl): void
	{
		$this->requestUrl = $requestUrl;
	}

	/**
	 * Gets the user agent.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getUserAgent(): string
	{
		return $this->userAgent;
	}

	/**
	 * Sets the user agent.
	 *
	 * @param string $userAgent
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function setUserAgent(string $userAgent): void
	{
		$this->userAgent = $userAgent;
	}

}
