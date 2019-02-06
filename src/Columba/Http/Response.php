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

namespace Columba\Http;

use SimpleXMLElement;

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
	 * @var string
	 */
	private $localIp;

	/**
	 * @var int
	 */
	private $localPort;

	/**
	 * @var string
	 */
	private $remoteIp;

	/**
	 * @var int
	 */
	private $remotePort;

	/**
	 * @var int
	 */
	private $responseCode;

	/**
	 * @var array
	 */
	private $responseHeaders;

	/**
	 * @var string
	 */
	private $responseText;

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

		$this->responseCode = -1;
		$this->responseHeaders = [];
		$this->responseText = '';

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

			$this->responseHeaders = HttpUtil::parseStringOfHeaders($headers);
			$this->responseText = $text;
		}
		else
		{
			throw new HttpException(curl_error($this->curlHandle), curl_errno($this->curlHandle));
		}

		$this->downloadSize = curl_getinfo($this->curlHandle, CURLINFO_SIZE_DOWNLOAD);
		$this->downloadSpeed = curl_getinfo($this->curlHandle, CURLINFO_SPEED_DOWNLOAD);
		$this->effectiveUrl = curl_getinfo($this->curlHandle, CURLINFO_EFFECTIVE_URL);
		$this->localIp = curl_getinfo($this->curlHandle, CURLINFO_LOCAL_IP);
		$this->localPort = curl_getinfo($this->curlHandle, CURLINFO_LOCAL_PORT);
		$this->remoteIp = curl_getinfo($this->curlHandle, CURLINFO_PRIMARY_IP);
		$this->remotePort = curl_getinfo($this->curlHandle, CURLINFO_PRIMARY_PORT);
		$this->responseCode = curl_getinfo($this->curlHandle, CURLINFO_RESPONSE_CODE);
		$this->transactionTime = curl_getinfo($this->curlHandle, CURLINFO_TOTAL_TIME);
		$this->uploadSize = curl_getinfo($this->curlHandle, CURLINFO_SIZE_UPLOAD);
		$this->uploadSpeed = curl_getinfo($this->curlHandle, CURLINFO_SPEED_UPLOAD);
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
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function asJson(): ?array
	{
		return json_decode($this->responseText, true);
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
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getLocalIp(): string
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
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getRemoteIp(): string
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
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getResponseHeaders(): array
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
