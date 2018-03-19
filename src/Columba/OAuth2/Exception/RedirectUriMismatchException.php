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
 * Class RedirectUriMismatchException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\OAuth2\Exception
 * @since 1.3.0
 */
final class RedirectUriMismatchException extends OAuth2Exception
{

	/**
	 * RedirectUriMismatchException constructor.
	 *
	 * @param string $message
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct (string $message = 'The redirect uri is missing or do not match.')
	{
		parent::__construct($message);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected final function getError (): string
	{
		return 'redirect_uri_mismatch';
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected final function getResponseCode (): int
	{
		return 400;
	}

}
