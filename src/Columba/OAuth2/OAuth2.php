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

namespace Columba\OAuth2;

use Columba\OAuth2\Client\Client;
use Columba\OAuth2\Client\IClientFactory;
use Columba\OAuth2\Exception\InvalidClientException;
use Columba\OAuth2\Exception\InvalidRequestException;
use Columba\OAuth2\Exception\InvalidScopeException;
use Columba\OAuth2\Exception\InvalidTokenException;
use Columba\OAuth2\Exception\OAuth2Exception;
use Columba\OAuth2\Exception\UnsupportedGrantTypeException;
use Columba\OAuth2\GrantType\AuthorizationCodeGrantType;
use Columba\OAuth2\GrantType\IGrantType;
use Columba\OAuth2\GrantType\RefreshTokenGrantType;
use Columba\OAuth2\ResponseType\CodeResponseType;
use Columba\OAuth2\ResponseType\IResponseType;
use Columba\OAuth2\ResponseType\TokenResponseType;
use Columba\OAuth2\Scope\IScopeFactory;
use Columba\OAuth2\Token\ITokenFactory;
use Exception;

/**
 * Class OAuth2
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\OAuth2
 * @since 1.3.0
 */
class OAuth2
{

	/**
	 * @var IClientFactory
	 */
	private $clientFactory;

	/**
	 * @var IScopeFactory
	 */
	private $scopeFactory;

	/**
	 * @var ITokenFactory
	 */
	private $tokenFactory;

	/**
	 * @var string[]
	 */
	private $grantTypes;

	/**
	 * @var string[]
	 */
	private $responseTypes;

	/**
	 * OAuth2 constructor.
	 *
	 * @param IClientFactory $clientFactory
	 * @param IScopeFactory  $scopeFactory
	 * @param ITokenFactory  $tokenFactory
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct (IClientFactory $clientFactory, IScopeFactory $scopeFactory, ITokenFactory $tokenFactory)
	{
		$this->clientFactory = $clientFactory;
		$this->clientFactory->setOAuth2($this);

		$this->scopeFactory = $scopeFactory;
		$this->scopeFactory->setOAuth2($this);

		$this->tokenFactory = $tokenFactory;
		$this->tokenFactory->setOAuth2($this);

		$this->grantTypes = [
			'authorization_code' => AuthorizationCodeGrantType::class,
			'refresh_token' => RefreshTokenGrantType::class
		];

		$this->responseTypes = [
			'code' => CodeResponseType::class,
			'token' => TokenResponseType::class
		];
	}

	/**
	 * Gets the {@see IClientFactory}.
	 *
	 * @return IClientFactory
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getClientFactory (): IClientFactory
	{
		return $this->clientFactory;
	}

	/**
	 * Gets the {@see IScopeFactoru}.
	 *
	 * @return IScopeFactory
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getScopeFactory (): IScopeFactory
	{
		return $this->scopeFactory;
	}

	/**
	 * Gets the {@see ITokenFactory}.
	 *
	 * @return ITokenFactory
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getTokenFactory (): ITokenFactory
	{
		return $this->tokenFactory;
	}

	/**
	 * Handles the authorize request.
	 *
	 * @param string      $clientId
	 * @param int         $ownerId
	 * @param string      $responseType
	 * @param string      $redirectUri
	 * @param string      $scope
	 * @param string|null $state
	 * @param bool        $authorize
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function handleAuthorizeRequest (string $clientId, int $ownerId, string $responseType, string $redirectUri, string $scope, ?string $state, bool $authorize = false): void
	{
		$client = $this->clientFactory->getClient($clientId);

		if (!$authorize)
		{
			http_response_code(303);
			header('Location: ' . $redirectUri . (strpos($redirectUri, '?') ? '&' : '?') . 'error=access_denied' . ($state !== null ? '&state=' . urlencode($state) : ''));
			return;
		}

		try
		{
			$responseTypeHandler = $this->responseTypes[$responseType] ?? null;

			if ($responseTypeHandler === null)
				die('INVALID_RESPONSE_TYPE: Unknown response_type.');

			/** @var IResponseType $responseTypeHandler */
			$responseTypeHandler = new $responseTypeHandler($this->tokenFactory);
			$responseTypeHandler->handleAuthorizeRequest($client, $ownerId, $redirectUri, $scope, $state);
		}
		catch (Exception $err)
		{
			header('Location: ' . $redirectUri . (strpos($redirectUri, '?') ? '&' : '?') . 'error=internal_error' . ($state !== null ? '&state=' . urlencode($state) : ''));
		}
	}

	/**
	 * Handles the token request.
	 *
	 * @return array
	 * @throws OAuth2Exception
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function handleTokenRequest (): array
	{
		$grantType = $_POST['grant_type'];

		/** @var IGrantType $grantTypeHandler */
		$grantTypeHandler = $this->grantTypes[$grantType];
		$grantTypeHandler = new $grantTypeHandler($this);

		return $grantTypeHandler->handleTokenRequest($grantType);
	}

	/**
	 * Validates the authorize request.
	 *
	 * @param string|null $clientId
	 * @param int         $ownerId
	 * @param string|null $responseType
	 * @param string|null $redirectUri
	 * @param string|null $scope
	 * @param Client|null $client
	 *
	 * @throws OAuth2Exception
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function validateAuthorizeRequest (?string $clientId, ?int $ownerId, ?string $responseType, ?string $redirectUri, ?string $scope, ?Client &$client): void
	{
		if ($clientId === null)
			throw new InvalidRequestException('Missing parameter: "client_id" is required.');

		if ($redirectUri === null)
			throw new InvalidRequestException('Missing parameter: "request_uri" is required.');

		if ($responseType === null)
			throw new InvalidRequestException('Missing parameter: "response_type" is required.');

		if ($scope === null)
			throw new InvalidRequestException('Missing parameter: "scope" is required.');

		$client = $this->clientFactory->getClient($clientId);

		if ($client === null || $ownerId === null)
			throw new InvalidClientException();

		if (!isset($this->responseTypes[$responseType]))
			throw new UnsupportedGrantTypeException();
	}

	/**
	 * Validates the Authorization header with client credentials.
	 *
	 * @return Client
	 * @throws InvalidClientException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function validateClientAuthorization (): Client
	{
		[
			$authorizationType,
			$authorizationCredentials
		] = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);

		if ($authorizationType !== 'Basic')
			throw new InvalidClientException();

		$authorizationCredentials = base64_decode($authorizationCredentials);
		$authorizationCredentials = explode(':', $authorizationCredentials);

		if (count($authorizationCredentials) !== 2)
			throw new InvalidClientException();

		[
			$clientId,
			$clientSecret
		] = $authorizationCredentials;

		$client = $this->clientFactory->getClient($clientId);

		if (!$client->isValidSecret($clientSecret))
			throw new InvalidClientException();

		return $client;
	}

	/**
	 * Validates a resource request.
	 *
	 * @return array
	 * @throws InvalidClientException
	 * @throws InvalidScopeException
	 * @throws InvalidTokenException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function validateResource (): array
	{
		if (!isset($_SERVER['HTTP_AUTHORIZATION']) || !strpos($_SERVER['HTTP_AUTHORIZATION'], ' '))
			throw new InvalidTokenException();

		[
			$authorizationType,
			$accessToken
		] = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);

		if ($authorizationType !== 'Bearer')
			throw new InvalidClientException();

		$accessToken = $this->tokenFactory->getToken('access_token', $accessToken);

		if ($accessToken === null)
			throw new InvalidTokenException();

		if ($accessToken->isExpired())
			throw new InvalidTokenException('The access_token has expired.');

		$client = $this->clientFactory->getClient($accessToken['client_id']);

		if ($client === null)
			throw new InvalidClientException();

		return [$accessToken['owner_id'], $this->scopeFactory->convertToScopes($accessToken['scope'])];
	}

	/**
	 * Validates the token request.
	 *
	 * @throws OAuth2Exception
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function validateTokenRequest (): void
	{
		/** @var string|null $grantType */
		$grantType = $_POST['grant_type'] ?? null;

		if ($grantType === null)
			throw new InvalidRequestException('Missing parameter: "grant_type" is required.');

		if (!isset($_SERVER['HTTP_AUTHORIZATION']))
			throw new InvalidRequestException('Missing header: "Authorization" is required.');

		if (!isset($this->grantTypes[$grantType]))
			throw new UnsupportedGrantTypeException();
	}

}
