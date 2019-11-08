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

use Columba\Foundation\System;
use Columba\Util\ArrayUtil;
use function array_filter;
use function array_map;
use function explode;
use function function_exists;
use function getallheaders;
use function implode;
use function strlen;
use function strtolower;
use function substr;

/**
 * Class HttpUtil
 *
 * @package Columba\Http
 * @author Bas Milius <bas@mili.us>
 * @since 1.2.0
 */
final class HttpUtil
{

	/**
	 * Gets all request headers.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public static function getAllRequestHeaders(): array
	{
		if (System::isCLI())
			return [];

		if (function_exists('getallheaders'))
		{
			$headers = getallheaders();
		}
		else
		{
			$headers = [];

			foreach ($_SERVER as $name => $value)
			{
				if (substr($name, 0, 5) !== 'HTTP_')
					continue;

				$name = substr($name, 5);
				$name = implode('-', array_map('strtolower', explode('_', $name)));

				$headers[$name] = $value;
			}
		}

		$result = [];

		foreach ($headers as $name => $value)
			$result[strtolower($name)] = $value;

		return $result;
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
