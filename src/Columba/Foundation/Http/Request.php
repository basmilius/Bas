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

namespace Columba\Foundation\Http;

use Columba\Facade\Jsonable;
use Columba\Foundation\Net\IP;
use Columba\Foundation\Store;
use Columba\Http\HttpUtil;
use Columba\Util\ArrayUtil;
use function array_merge;
use function array_pop;
use function array_shift;
use function count;
use function explode;
use function file_get_contents;
use function fseek;
use function fwrite;
use function json_decode;
use function ltrim;
use function parse_str;
use function preg_match;
use function preg_split;
use function rtrim;
use function strstr;
use function tmpfile;

/**
 * Class Request
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Foundation\Http
 * @since 1.5.0
 */
class Request implements Jsonable
{

	protected Store $localStorage;
	protected Parameters $cookies;
	protected Parameters $files;
	protected Parameters $headers;
	protected Parameters $post;
	protected QueryString $queryString;
	protected Parameters $server;

	/**
	 * Request constructor.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function __construct()
	{
		$this->localStorage = new Store();

		$this->cookies = new Parameters($_COOKIE ?? []);
		$this->files = new Parameters($_FILES ?? []);
		$this->headers = new Parameters(HttpUtil::getAllRequestHeaders());
		$this->post = new Parameters($_POST ?? []);
		$this->queryString = QueryString::createFromString($_SERVER['QUERY_STRING'] ?? '');
		$this->server = new Parameters($_SERVER ?? []);
	}

	/**
	 * Gets the request body as string.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function body(): string
	{
		return $this->localStorage->getOrCreate(__METHOD__, fn() => file_get_contents('php://input'));
	}

	/**
	 * Gets the request body as json.
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function bodyJson()
	{
		return $this->localStorage->getOrCreate(__METHOD__, fn() => json_decode($this->body(), true));
	}

	/**
	 * Gets the request body and parses it as multi part.
	 *
	 * @return array|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function bodyMultiPart(): ?array
	{
		return $this->localStorage->getOrCreate(__METHOD__, function (): ?array
		{
			$queryString = '';
			$result = [];

			preg_match('/boundary=(.*)$/', $this->server->get('CONTENT_TYPE'), $matches);
			$boundary = $matches[1] ?? null;

			if ($boundary === null)
				return null; // Not a multipart request.

			$blocks = preg_split('/-+' . $boundary . '/', $this->body());
			array_pop($blocks);

			$addItem = static function (string $name, $data) use (&$result): void
			{
				if (isset($result[$name]))
					if (ArrayUtil::isSequentialArray($result[$name]))
						$result[$name][] = $data;
					else
						$result[$name] = [$result[$name], $data];
				else
					$result[$name] = $data;
			};

			$parseContentDisposition = static function (string $str): array
			{
				$raw = preg_split('/(?<!\d)(; )/', $str);
				array_shift($raw);

				$params = [];

				foreach ($raw as $param)
				{
					$p = explode('=', $param, 2);
					$params[$p[0]] = ltrim(rtrim($p[1], '"'), '"');
				}

				return $params;
			};

			while (count($blocks) > 0)
			{
				$block = array_shift($blocks);

				if (empty($block))
					continue;

				[$headers, $body] = preg_split("/(\r\n\r\n|\n\n|\r\r)/", $block, 2);

				$headers = HttpUtil::parseStringOfHeaders($headers);

				if (!isset($headers['content-disposition']) || !isset($headers['content-type']))
					continue;

				$contentDisposition = $headers['content-disposition'];
				$contentType = $headers['content-type'];

				$this->handleMultiPartData($addItem, $body, $headers, $contentType, $parseContentDisposition($contentDisposition));
			}

			parse_str($queryString, $queryStringArray);

			return array_merge($result, $queryStringArray);
		});
	}

	/**
	 * Gets the cookie collection.
	 *
	 * @return Parameters
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function cookies(): Parameters
	{
		return $this->cookies;
	}

	/**
	 * Gets the files collection.
	 *
	 * @return Parameters
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function files(): Parameters
	{
		return $this->files;
	}

	/**
	 * Gets the header collection.
	 *
	 * @return Parameters
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function headers(): Parameters
	{
		return $this->headers;
	}

	/**
	 * Gets the POST parameter collection.
	 *
	 * @return Parameters
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function post(): Parameters
	{
		return $this->post;
	}

	/**
	 * Gets the querystring collection.
	 *
	 * @return QueryString
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function queryString(): QueryString
	{
		return $this->queryString;
	}

	/**
	 * Gets the server collection.
	 *
	 * @return Parameters
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function server(): Parameters
	{
		return $this->server;
	}

	/**
	 * Gets the bearer token of the request, if available.
	 *
	 * @return string|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function bearerToken(): ?string
	{
		$header = $this->headers->get('authorization');

		if ($header === null)
			return null;

		$parts = explode(' ', $header, 2);

		if (count($parts) !== 2 || $parts[0] !== 'Bearer')
			return null;

		return $parts[1];
	}

	/**
	 * Gets the request ip.
	 *
	 * @return IP|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function ip(): ?IP
	{
		return $this->localStorage->getOrCreate(__METHOD__, fn(): IP => IP::parse($this->server->get('REMOTE_ADDR')));
	}

	/**
	 * Returns TRUE if this is a secure request.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function isSecure(): bool
	{
		return $this->server->get('HTTPS', 'off') === 'on';
	}

	/**
	 * Gets the request accepted languages.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function languages(): array
	{
		$accept = $this->headers->get('accept-language', 'en');
		$accept = explode(',', $accept);
		$languages = [];

		if (isset($_GET['language']) && strlen($_GET['language']) === 2 && ctype_alpha($_GET['language']))
			$languages[] = ['q' => '2.0', 'cca2' => $_GET['language'], 'code' => $_GET['language']];

		foreach ($accept as $lang)
		{
			$lang = explode(';', $lang);
			parse_str($lang[1] ?? 'q=1.0', $props);

			$props['cca2'] = explode('-', $lang[0])[0];
			$props['code'] = $lang[0];

			$languages[] = $props;
		}

		return $languages;
	}

	/**
	 * Gets the request method.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function method(): string
	{
		return $this->server->get('REQUEST_METHOD', 'GET');
	}

	/**
	 * Gets the request path name.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function pathName(): string
	{
		$uri = $this->uri();

		return strstr($uri, '?', true) ?: $uri;
	}

	/**
	 * Gets the request uri.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function uri(): string
	{
		return $this->server->get('REQUEST_URI');
	}

	/**
	 * Gets the user agent.
	 *
	 * @return UserAgent
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function userAgent(): UserAgent
	{
		return $this->localStorage->getOrCreate(__METHOD__, fn(): UserAgent => new UserAgent($this->server->get('HTTP_USER_AGENT')));
	}

	/**
	 * Handles multi part data. Override this method to add your own.
	 *
	 * @param callable $addItem
	 * @param string $body
	 * @param array $headers
	 * @param string $contentType
	 * @param array $contentDisposition
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	protected function handleMultiPartData(callable $addItem, string $body, array $headers, string $contentType, array $contentDisposition): void
	{
		switch ($headers['content-type'])
		{
			case 'application/json':
				$addItem($contentDisposition['name'], json_decode($body, true));
				break;

			case 'image/gif':
			case 'image/jpg':
			case 'image/jpeg':
			case 'image/png':
				$file = tmpfile();
				fwrite($file, $body);
				fseek($file, 0);

				$addItem($contentDisposition['name'], [
					'name' => $contentDisposition['filename'],
					'type' => $contentType,
					'stream' => $file
				]);
				break;

			default:
				break;
		}
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function jsonSerialize(): array
	{
		return [
			'headers' => $this->headers,
			'post' => $this->post,
			'query_string' => $this->queryString
		];
	}

}
