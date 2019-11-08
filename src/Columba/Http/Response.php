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

use Columba\Foundation\Http\Parameters;
use Columba\Foundation\Net\IP;
use Columba\Foundation\Net\IPException;
use SimpleXMLElement;
use const CURLINFO_EFFECTIVE_URL;
use const CURLINFO_HEADER_SIZE;
use const CURLINFO_LOCAL_IP;
use const CURLINFO_LOCAL_PORT;
use const CURLINFO_PRIMARY_IP;
use const CURLINFO_PRIMARY_PORT;
use const CURLINFO_RESPONSE_CODE;
use const CURLINFO_SIZE_DOWNLOAD;
use const CURLINFO_SIZE_UPLOAD;
use const CURLINFO_SPEED_DOWNLOAD;
use const CURLINFO_SPEED_UPLOAD;
use const CURLINFO_TOTAL_TIME;
use function curl_close;
use function curl_errno;
use function curl_error;
use function curl_exec;
use function curl_getinfo;
use function imagecreatefromstring;
use function json_decode;
use function simplexml_load_string;
use function substr;

/**
 * Class Response
 *
 * @package Columba\Http
 * @author Bas Milius <bas@mili.us>
 * @since 1.2.0
 */
final class Response
{

	/**
	 * @var resource
	 */
	private $curlHandle;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var int
	 */
	private $downloadSize;

	/**
	 * @var int
	 */
	private $downloadSpeed;

	/**
	 * @var string
	 */
	private $effectiveUrl;

	/**
	 * @var IP|null
	 */
	private $localIp = null;

	/**
	 * @var int|null
	 */
	private $localPort = null;

	/**
	 * @var IP|null
	 */
	private $remoteIp = null;

	/**
	 * @var int|null
	 */
	private $remotePort = null;

	/**
	 * @var int|null
	 */
	private $responseCode = null;

	/**
	 * @var Parameters|null
	 */
	private $responseHeaders = null;

	/**
	 * @var string|null
	 */
	private $responseText = null;

	/**
	 * @var int
	 */
	private $transactionTime;

	/**
	 * @var int
	 */
	private $uploadSize;

	/**
	 * @var int
	 */
	private $uploadSpeed;

	/**
	 * Response constructor.
	 *
	 * @param Request  $request
	 * @param resource $curlHandle
	 *
	 * @throws HttpException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public function __construct(Request $request, $curlHandle)
	{
		$this->curlHandle = $curlHandle;
		$this->request = $request;

		$this->parseResponse();
	}

	/**
	 * Parses the response.
	 *
	 * @throws HttpException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	private function parseResponse(): void
	{
		$response = curl_exec($this->curlHandle);

		if ($response)
		{
			$headerSize = curl_getinfo($this->curlHandle, CURLINFO_HEADER_SIZE);

			$headers = substr($response, 0, $headerSize);
			$text = substr($response, $headerSize);

			$this->responseHeaders = new Parameters(HttpUtil::parseStringOfHeaders($headers));
			$this->responseText = $text;
		}
		else
		{
			throw new HttpException(curl_error($this->curlHandle), curl_errno($this->curlHandle));
		}

		try
		{
			$this->localIp = IP::parse(curl_getinfo($this->curlHandle, CURLINFO_LOCAL_IP), false);
			$this->remoteIp = IP::parse(curl_getinfo($this->curlHandle, CURLINFO_PRIMARY_IP), false);
		}
		catch (IPException $err)
		{
		}

		$this->downloadSize = curl_getinfo($this->curlHandle, CURLINFO_SIZE_DOWNLOAD);
		$this->downloadSpeed = curl_getinfo($this->curlHandle, CURLINFO_SPEED_DOWNLOAD);
		$this->effectiveUrl = curl_getinfo($this->curlHandle, CURLINFO_EFFECTIVE_URL);
		$this->localPort = curl_getinfo($this->curlHandle, CURLINFO_LOCAL_PORT);
		$this->remotePort = curl_getinfo($this->curlHandle, CURLINFO_PRIMARY_PORT);
		$this->responseCode = curl_getinfo($this->curlHandle, CURLINFO_RESPONSE_CODE);
		$this->transactionTime = curl_getinfo($this->curlHandle, CURLINFO_TOTAL_TIME);
		$this->uploadSize = curl_getinfo($this->curlHandle, CURLINFO_SIZE_UPLOAD);
		$this->uploadSpeed = curl_getinfo($this->curlHandle, CURLINFO_SPEED_UPLOAD);

		curl_close($this->curlHandle);
	}

	/**
	 * Gets the response as an image resource.
	 *
	 * @return resource
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function asImageResource()
	{
		return imagecreatefromstring($this->responseText);
	}

	/**
	 * Gets the response as JSON.
	 *
	 * @param bool $assoc
	 *
	 * @return array|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function asJson(bool $assoc = true): ?array
	{
		return json_decode($this->responseText, $assoc);
	}

	/**
	 * Gets the response as text.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function asText(): string
	{
		return $this->responseText;
	}

	/**
	 * Gets the response as XML.
	 *
	 * @return SimpleXMLElement|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function asXml(): ?SimpleXMLElement
	{
		$xml = simplexml_load_string($this->responseText);

		if (!$xml)
			return null;

		return $xml;
	}

	/**
	 * Gets the {@see Request}.
	 *
	 * @return Request
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getRequest(): Request
	{
		return $this->request;
	}

	/**
	 * Gets the download size in bytes.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getDownloadSize(): int
	{
		return $this->downloadSize;
	}

	/**
	 * Gets the download speed in bytes/s.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getDownloadSpeed(): int
	{
		return $this->downloadSpeed;
	}

	/**
	 * Gets the last effective URL.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getEffectiveUrl(): string
	{
		return $this->effectiveUrl;
	}

	/**
	 * Gets the local ip address.
	 *
	 * @return IP|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getLocalIp(): ?IP
	{
		return $this->localIp;
	}

	/**
	 * Gets the local port.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getLocalPort(): int
	{
		return $this->localPort;
	}

	/**
	 * Gets the remote ip.
	 *
	 * @return IP|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getRemoteIp(): ?IP
	{
		return $this->remoteIp;
	}

	/**
	 * Gets the remote port.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getRemotePort(): int
	{
		return $this->remotePort;
	}

	/**
	 * Gets the response code.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getResponseCode(): int
	{
		return $this->responseCode;
	}

	/**
	 * Gets the response headers.
	 *
	 * @return Parameters|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getResponseHeaders(): ?Parameters
	{
		return $this->responseHeaders;
	}

	/**
	 * Gets the response text.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getResponseText(): string
	{
		return $this->responseText;
	}

	/**
	 * Gets the transaction time.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getTransactionTime(): int
	{
		return $this->transactionTime;
	}

	/**
	 * Gets the upload size in bytes.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getUploadSize(): int
	{
		return $this->uploadSize;
	}

	/**
	 * Gets the upload speed in bytes/s.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getUploadSpeed(): int
	{
		return $this->uploadSpeed;
	}

}
