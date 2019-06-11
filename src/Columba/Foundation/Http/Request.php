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

namespace Columba\Foundation\Http;

use Columba\Facade\IJson;
use Columba\Foundation\System;
use Columba\Http\HttpUtil;
use Columba\Util\ArrayUtil;

/**
 * Class Request
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Foundation\Http
 * @since 1.5.0
 */
class Request implements IJson
{

	/**
	 * @var string|null
	 */
	protected $body = null;

	/**
	 * @var HeaderParameters
	 */
	protected $headers;

	/**
	 * @var PostParameters
	 */
	protected $post;

	/**
	 * @var QueryString
	 */
	protected $queryString;

	/**
	 * Request constructor.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function __construct()
	{
		$this->headers = new HeaderParameters(HttpUtil::getAllRequestHeaders());
		$this->queryString = new QueryString($_GET ?? []);
		$this->post = new PostParameters($_POST ?? []);
	}

	/**
	 * Gets the request body as string.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function getBody(): string
	{
		return $this->body ?? $this->body = file_get_contents('php://input');
	}

	/**
	 * Gets the request body as json.
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function getBodyJson()
	{
		return json_decode($this->getBody(), true);
	}

	/**
	 * Gets the request body and parses it as multi part.
	 *
	 * @return array|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function getBodyMultiPart()
	{
		$queryString = '';
		$result = [];

		preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
		$boundary = $matches[1] ?? null;

		if ($boundary === null)
			return null; // Not a multipart request.

		$blocks = preg_split('/-+' . $boundary . '/', $this->getBody());
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
	}

	/**
	 * Gets the request headers.
	 *
	 * @return HeaderParameters
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function getHeaders(): HeaderParameters
	{
		return $this->headers;
	}

	/**
	 * Gets the POST parameters instance.
	 *
	 * @return PostParameters
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function getPost(): PostParameters
	{
		return $this->post;
	}

	/**
	 * Gets the QueryString instance.
	 *
	 * @return QueryString
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function getQueryString(): QueryString
	{
		return $this->queryString;
	}

	/**
	 * Gets the request uri.
	 *
	 * @return string|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function getRequestUri(): ?string
	{
		if (System::isCLI())
			return null;

		return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	/**
	 * Handles multi part data. Override this method to add your own.
	 *
	 * @param callable $addItem
	 * @param string   $body
	 * @param array    $headers
	 * @param string   $contentType
	 * @param array    $contentDisposition
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
