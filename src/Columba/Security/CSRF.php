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

namespace Columba\Security;

use Columba\Security\JWT\JWT;
use Columba\Security\JWT\JWTException;

/**
 * Class CSRF
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Security
 * @since 1.6.0
 */
class CSRF
{

	private string $requestId;
	private string $secret;

	/**
	 * Csrf constructor.
	 *
	 * @param string $requestId
	 * @param string $secret
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct(string $requestId, string $secret)
	{
		$this->requestId = $requestId;
		$this->secret = $secret;
	}

	/**
	 * Generates a CSRF token.
	 *
	 * @param array $payload
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function generate(array $payload = []): string
	{
		$payload['request_id'] = $this->requestId;

		return JWT::encode($payload, $this->secret, 'HS384');
	}

	/**
	 * Validates the given CSRF token.
	 *
	 * @param string $token
	 * @param array|null $payload
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function validate(string $token, ?array &$payload = null): bool
	{
		try
		{
			$payload = JWT::decode($token, [$this->secret]);

			if (!isset($payload['request_id']) || $payload['request_id'] !== $this->requestId)
				return false;

			return true;
		}
		catch (JWTException $err)
		{
			$payload = null;

			return false;
		}
	}

	/**
	 * Creates a CSRF instance with the given request id and secret key.
	 *
	 * @param string $requestId
	 * @param string $secret
	 *
	 * @return static
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function with(string $requestId, string $secret): self
	{
		return new static($requestId, $secret);
	}

}
