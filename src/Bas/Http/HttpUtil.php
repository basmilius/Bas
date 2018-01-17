<?php
declare(strict_types=1);

namespace Bas\Http;

use Bas\Util\ArrayUtil;

/**
 * Class HttpUtil
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Http
 * @since 1.2.0
 */
final class HttpUtil
{

	/**
	 * Parses an array with headers to an valid array with headers.
	 *
	 * @param array $headers
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public static function parseArrayOfHeaders (array $headers): array
	{
		$httpHeaders = [];

		if (ArrayUtil::isSequentialArray($headers))
			return $headers;

		foreach ($headers as $name => $value)
			$httpHeaders[] = "$name: $value";

		return $httpHeaders;
	}

	public static function parseStringOfHeaders (string $headersString): array
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
