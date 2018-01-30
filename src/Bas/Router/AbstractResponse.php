<?php
/**
 * This file is part of the Bas package.
 *
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bas\Router;

/**
 * Class AbstractResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Router
 * @since 1.0.0
 */
abstract class AbstractResponse
{

	/**
	 * Prints {@see $data} to the output buffer.
	 *
	 * @param mixed $data
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public abstract function print ($data): void;

	/**
	 * Redirects to {@see $redirectUri} using {@see $code} as HTTP response code.
	 *
	 * @param string $redirectUri
	 * @param int    $code
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function redirect (string $redirectUri, int $code = 301): void
	{
		$queryString = explode('?', $_SERVER['REQUEST_URI'])[1] ?? '';
		if (strlen($queryString) > 0)
			$queryString = '?' . $queryString;

		http_response_code($code);
		header('Location: ' . $redirectUri . $queryString);

		die;
	}

}
