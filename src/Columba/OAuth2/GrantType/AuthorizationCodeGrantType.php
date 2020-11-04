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

namespace Columba\OAuth2\GrantType;

use Columba\OAuth2\Exception\InvalidGrantException;
use Columba\OAuth2\Exception\InvalidRequestException;
use Columba\OAuth2\Exception\RedirectUriMismatchException;
use Columba\OAuth2\OAuth2;
use Columba\OAuth2\Token\TokenGenerator;

/**
 * Class AuthorizationCodeGrantType
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\OAuth2\GrantType
 * @since 1.3.0
 */
final class AuthorizationCodeGrantType implements IGrantType
{

	private OAuth2 $oAuth2;

	/**
	 * AuthorizationCodeGrantType constructor.
	 *
	 * @param OAuth2 $oAuth2
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(OAuth2 $oAuth2)
	{
		$this->oAuth2 = $oAuth2;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function handleTokenRequest(string $grantType): array
	{
		$client = $this->oAuth2->validateClientAuthorization();

		/** @var string|null $code */
		$code = $_POST['code'] ?? null;

		/** @var string|null $code */
		$redirectUri = $_POST['redirect_uri'] ?? null;

		if ($code === null)
			throw new InvalidRequestException('Missing parameter: "code" is required.');

		if ($redirectUri === null)
			throw new InvalidRequestException('Missing parameter: "redirect_uri" is required.');

		$authorizationCode = $this->oAuth2->getTokenFactory()->getToken('authorization_code', $code);
		$redirectUri = urldecode($redirectUri);

		if ($authorizationCode === null)
			throw new InvalidGrantException('Authorization code doesn\'t exist or is invalid for the client.');

		if ($authorizationCode->isExpired())
			throw new InvalidGrantException('The authorization code has expired.');

		if ($authorizationCode['redirect_uri'] !== $redirectUri)
			throw new RedirectUriMismatchException();

		$accessToken = TokenGenerator::generateSimpleToken();
		$refreshToken = TokenGenerator::generateSimpleToken();

		$this->oAuth2->getTokenFactory()->saveRefreshToken($client['client_id'], $authorizationCode['owner_id'], $authorizationCode['scope'], $refreshToken);
		$this->oAuth2->getTokenFactory()->saveAccessToken($client['client_id'], $authorizationCode['owner_id'], $authorizationCode['scope'], $accessToken, $refreshToken);
		$this->oAuth2->getTokenFactory()->expireToken($client['client_id'], 'authorization_code', $authorizationCode['token']);

		return [
			'access_token' => $accessToken,
			'token_type' => 'Bearer',
			'scope' => $authorizationCode['scope'],
			'expires_in' => 3600,
			'refresh_token' => $refreshToken
		];
	}

}
