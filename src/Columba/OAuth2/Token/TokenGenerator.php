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

namespace Columba\OAuth2\Token;

use Exception;
use function base64_encode;
use function hash;
use function openssl_random_pseudo_bytes;
use function random_bytes;

/**
 * Class TokenGenerator
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\OAuth2\Token
 * @since 1.3.0
 */
final class TokenGenerator
{

	/**
	 * Creates a simple token.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public static function generateBase64Token(): string
	{
		return base64_encode(self::randomBytes(100));
	}

	/**
	 * Creates a simple token.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public static function generateSimpleToken(): string
	{
		return hash('whirlpool', self::randomBytes(100));
	}

	/**
	 * Returns random bytes.
	 *
	 * @param int $amount
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	private static function randomBytes(int $amount): string
	{
		try
		{
			return random_bytes($amount);
		}
		catch (Exception $err)
		{
			return openssl_random_pseudo_bytes($amount);
		}
	}

}
