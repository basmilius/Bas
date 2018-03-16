<?php
/**
 * Copyright © 2018 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Latte Framework package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\OAuth2\ResponseType;

use Columba\OAuth2\Client\Client;
use Columba\OAuth2\Token\ITokenFactory;
use Columba\OAuth2\Token\TokenGenerator;

/**
 * Class TokenResponseType
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\OAuth2\ResponseType
 * @since 1.3.0
 */
final class TokenResponseType implements IResponseType
{

	/**
	 * @var ITokenFactory
	 */
	private $tokenFactory;

	/**
	 * TokenResponseType constructor.
	 *
	 * @param ITokenFactory $tokenFactory
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct (ITokenFactory $tokenFactory)
	{
		$this->tokenFactory = $tokenFactory;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function handleAuthorizeRequest (Client $client, int $ownerId, string $redirectUri, string $scope, ?string $state): void
	{
		$accessToken = TokenGenerator::generateSimpleToken();

		$this->tokenFactory->saveAccessToken($client['client_id'], $ownerId, $scope, $accessToken);

		http_response_code(303);
		header('Location: ' . $redirectUri . '#code=' . $accessToken . '&token_type=Bearer&expires_in=3600' . ($state !== null ? '&state=' . urlencode($state) : ''));
	}

}
