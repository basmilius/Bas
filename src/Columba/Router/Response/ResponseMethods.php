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

namespace Columba\Router\Response;

/**
 * Trait ResponseMethods
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router\Response
 * @since 1.5.0
 */
trait ResponseMethods
{

	/**
	 * Returns a html response.
	 *
	 * @param string $data
	 *
	 * @return ResponseWrapper
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 *
	 * @see HtmlResponse
	 */
	protected final function html(string $data): ResponseWrapper
	{
		return $this->respond(HtmlResponse::class, $data);
	}

	/**
	 * Returns a javascript response.
	 *
	 * @param string $data
	 *
	 * @return ResponseWrapper
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 *
	 * @see JavaScriptResponse
	 */
	protected final function javascript(string $data): ResponseWrapper
	{
		return $this->respond(JavaScriptResponse::class, $data);
	}

	/**
	 * Returns a json response.
	 *
	 * @param mixed $data
	 * @param bool  $withDefaults
	 * @param int   $options
	 *
	 * @return ResponseWrapper
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 *
	 * @see JsonResponse
	 */
	protected final function json($data, bool $withDefaults = true, int $options = JsonResponse::DEFAULT_OPTIONS): ResponseWrapper
	{
		return $this->respond(JsonResponse::class, $data, $withDefaults, $options);
	}

	/**
	 * Returns a plain response.
	 *
	 * @param mixed $data
	 *
	 * @return ResponseWrapper
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 *
	 * @see PlainResponse
	 */
	protected final function plain($data): ResponseWrapper
	{
		return $this->respond(PlainResponse::class, $data);
	}

	/**
	 * Returns a serialize response.
	 *
	 * @param mixed $data
	 *
	 * @return ResponseWrapper
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 *
	 * @see SerializeResponse
	 */
	protected final function serialize($data): ResponseWrapper
	{
		return $this->respond(SerializeResponse::class, $data);
	}

	/**
	 * Returns a XML response.
	 *
	 * @param mixed  $data
	 * @param bool   $withDefaults
	 * @param bool   $prettyPrint
	 * @param string $root
	 *
	 * @return ResponseWrapper
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 *
	 * @see XmlResponse
	 */
	protected final function xml($data, bool $withDefaults = true, bool $prettyPrint = false, string $root = XmlResponse::ROOT): ResponseWrapper
	{
		return $this->respond(XmlResponse::class, $data, $withDefaults, $prettyPrint, $root);
	}

}
