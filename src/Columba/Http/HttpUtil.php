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

use Columba\Util\ArrayUtil;

/**
 * Class HttpUtil
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Http
 * @since 1.2.0
 */
final class HttpUtil
{

	/**
	 * Converts a status code to text.
	 *
	 * @param int $code
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public static function convertStatusCodeToText(int $code = 0): string
	{
		$statusText = [
			100 => 'Continue',
			101 => 'Switching Protocols',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			300 => 'Multiple Choices',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported'
		];

		return $statusText[$code] ?? '';
	}

	/**
	 * Parses an array with headers to an valid array with headers.
	 *
	 * @param array $headers
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public static function parseArrayOfHeaders(array $headers): array
	{
		$httpHeaders = [];

		if (ArrayUtil::isSequentialArray($headers))
			return $headers;

		foreach ($headers as $name => $value)
			$httpHeaders[] = "$name: $value";

		return $httpHeaders;
	}

	/**
	 * Parses a string of headers.
	 *
	 * @param string $headersString
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public static function parseStringOfHeaders(string $headersString): array
	{
		$headers = [];

		$rawHeaders = explode(PHP_EOL, $headersString);
		$rawHeaders = array_filter($rawHeaders);

		foreach ($rawHeaders as $rawHeader)
		{
			$header = explode(':', $rawHeader, 2);
			$header = array_map('trim', $header);

			if (strlen($header[0]) === 0 || !isset($header[1]))
				continue;

			$headers[strtolower($header[0])] = $header[1];
		}

		return $headers;
	}

}
