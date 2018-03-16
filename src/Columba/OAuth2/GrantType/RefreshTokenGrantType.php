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

namespace Columba\OAuth2\GrantType;

use Columba\OAuth2\Exception\InvalidGrantException;
use Columba\OAuth2\Exception\InvalidRequestException;
use Columba\OAuth2\OAuth2;
use Columba\OAuth2\Token\Token;
use Columba\OAuth2\Token\TokenGenerator;

/**
 * Class RefreshTokenGrantType
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\OAuth2\GrantType
 * @since 1.3.0
 */
final class RefreshTokenGrantType implements IGrantType
{

	/**
	 * @var OAuth2
	 */
	private $oAuth2;

	/**
	 * RefreshTokenGrantType constructor.
	 *
	 * @param OAuth2 $oAuth2
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct (OAuth2 $oAuth2)
	{
		$this->oAuth2 = $oAuth2;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function handleTokenRequest (string $grantType): array
	{
		$client = $this->oAuth2->validateClientAuthorization();

		/** @var string|null $refreshToken */
		$refreshToken = $_POST['refresh_token'] ?? null;

		if ($refreshToken === null)
			throw new InvalidRequestException('Missing parameter: "refresh_token" is required.');

		$refreshToken = $this->oAuth2->getTokenFactory()->getToken('refresh_token', $refreshToken);

		/** @var Token|null $refreshToken */
		if ($refreshToken === null || $refreshToken->isExpired())
			throw new InvalidGrantException('The refresh token has expired.');

		$accessToken = TokenGenerator::generateSimpleToken();
		$oldAccessToken = $this->oAuth2->getTokenFactory()->getAccessTokenByRefreshToken($client['client_id'], $refreshToken['token']);

		$this->oAuth2->getTokenFactory()->saveAccessToken($client['client_id'], $oldAccessToken['owner_id'], $oldAccessToken['scope'], $accessToken, $refreshToken['token'], 3600);
		$this->oAuth2->getTokenFactory()->expireToken($client['client_id'], 'access_token', $oldAccessToken['token']);

		return [
			'access_token' => $accessToken,
			'token_type' => 'Bearer',
			'scope' => $oldAccessToken['scope'],
			'expires_in' => 3600
		];
	}

}
