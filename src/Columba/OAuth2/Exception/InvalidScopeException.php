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

namespace Columba\OAuth2\Exception;

/**
 * Class InvalidScopeException
 *
 * @package Columba\OAuth2\Exception
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.0
 */
final class InvalidScopeException extends OAuth2Exception
{

	/**
	 * InvalidScopeException constructor.
	 *
	 * @param string $message
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(string $message)
	{
		parent::__construct($message);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected final function getError(): string
	{
		return 'invalid_scope';
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected final function getResponseCode(): int
	{
		return 400;
	}

}
