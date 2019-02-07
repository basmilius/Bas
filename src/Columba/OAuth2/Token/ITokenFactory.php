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

namespace Columba\OAuth2\Token;

use Columba\OAuth2\OAuth2;

/**
 * Interface ITokenFactory
 *
 * @package Columba\OAuth2\Token
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.0
 */
interface ITokenFactory
{

	/**
	 * Expires a token.
	 *
	 * @param string $clientId
	 * @param string $type
	 * @param string $token
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	function expireToken(string $clientId, string $type, string $token): void;

	/**
	 * Gets an access token by refresh token.
	 *
	 * @param string $clientId
	 * @param string $refreshToken
	 *
	 * @return Token|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	function getAccessTokenByRefreshToken(string $clientId, string $refreshToken): ?Token;

	/**
	 * Gets a {@see Token}.
	 *
	 * @param string $type
	 * @param string $token
	 *
	 * @return Token|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	function getToken(string $type, string $token): ?Token;

	/**
	 * Saves an access token.
	 *
	 * @param string      $clientId
	 * @param int         $ownerId
	 * @param string      $scope
	 * @param string      $token
	 * @param string|null $associatedToken
	 * @param int         $expiresIn
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	function saveAccessToken(string $clientId, int $ownerId, string $scope, string $token, ?string $associatedToken = null, int $expiresIn = 3600): string;

	/**
	 * Saves an authorization token.
	 *
	 * @param string $clientId
	 * @param int    $ownerId
	 * @param string $redirectUri
	 * @param string $scope
	 * @param string $token
	 * @param int    $expiresIn
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	function saveAuthorizationToken(string $clientId, int $ownerId, string $redirectUri, string $scope, string $token, int $expiresIn = 60): string;

	/**
	 * Saves a refresh token.
	 *
	 * @param string $clientId
	 * @param int    $ownerId
	 * @param string $scope
	 * @param string $token
	 * @param int    $expiresIn
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	function saveRefreshToken(string $clientId, int $ownerId, string $scope, string $token, int $expiresIn = -1): string;

	/**
	 * Sets the OAuth2 instance.
	 *
	 * @param OAuth2 $oAuth2
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	function setOAuth2(OAuth2 $oAuth2): void;

}
