<?php
declare(strict_types=1);

namespace Bas\Http;

/**
 * Class Request
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Http
 * @since 1.2.0
 */
class Request
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
	 * @var string|null
	 */
	protected $requestMethod;

	/**
	 * @var string|null
	 */
	protected $requestUrl;

	/**
	 * Request constructor.
	 *
	 * @param string|null $requestUrl
	 * @param string|null $requestMethod
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public function __construct (?string $requestUrl = null, ?string $requestMethod = null)
	{
		$this->body = null;
		$this->headers = [];

		$this->requestMethod = $requestMethod;
		$this->requestUrl = $requestUrl;
	}

}
