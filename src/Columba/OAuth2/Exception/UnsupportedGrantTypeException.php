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

namespace Columba\OAuth2\Exception;

/**
 * Class UnsupportedGrantTypeException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\OAuth2\Exception
 * @since 1.3.0
 */
final class UnsupportedGrantTypeException extends OAuth2Exception
{

	/**
	 * UnsupportedGrantTypeException constructor.
	 *
	 * @param string $message
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct (string $message = 'The authorization grant type is not supported by the authorization server.')
	{
		parent::__construct($message);
	}

	/**
	 * Gets the error type.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected final function getError (): string
	{
		return 'unsupported_grant_type';
	}

	/**
	 * Gets the response code.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected final function getResponseCode (): int
	{
		return 400;
	}

}
