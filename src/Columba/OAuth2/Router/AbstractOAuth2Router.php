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

namespace Columba\OAuth2\Router;

use Columba\OAuth2\OAuth2;
use Columba\Router\Renderer\AbstractRenderer;
use Columba\Router\Response\AbstractResponse;
use Columba\Router\Response\HtmlResponse;
use Columba\Router\Response\JsonResponse;

/**
 * Class AbstractOAuth2Router
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\OAuth2\Router
 * @since 1.3.0
 */
abstract class AbstractOAuth2Router extends AbstractOAuth2AwareRouter
{

	/**
	 * AbstractOAuth2Router constructor.
	 *
	 * @param OAuth2                $oAuth2
	 * @param AbstractResponse|null $response
	 * @param AbstractRenderer|null $renderer
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct (OAuth2 $oAuth2, ?AbstractResponse $response = null, ?AbstractRenderer $renderer = null)
	{
		parent::__construct($oAuth2, $response, $renderer);

		$this->for('/authorize', [
			HTTP_GET => [[$this, 'onGetOAuth2Authorize'], new HtmlResponse()],
			HTTP_POST => [[$this, 'onPostOAuth2Authorize'], new JsonResponse(false)]
		]);

		$this->post('/token', [$this, 'onPostOAuth2Token'], new JsonResponse(false));
	}

	/**
	 * Invoked when GET /authorize is requested.
	 *
	 * @param string|null $client_id
	 * @param string|null $redirect_uri
	 * @param string|null $response_type
	 * @param string|null $scope
	 * @param string|null $state
	 *
	 * @return string
	 * @throws \Columba\OAuth2\Exception\OAuthException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 * @internal
	 */
	public final function onGetOAuth2Authorize (?string $client_id = null, ?string $redirect_uri = null, ?string $response_type = null, ?string $scope = null, ?string $state = null): string
	{
		$this->oAuth2->validateAuthorizeRequest($client_id, $this->getOwnerId(), $response_type, $redirect_uri, $scope, $client);

		return $this->renderAuthorize([
			'client' => $client,
			'client_id' => $client_id,
			'redirect_uri' => $redirect_uri,
			'response_type' => $response_type,
			'scope' => $scope,
			'scopes' => $this->oAuth2->getScopeFactory()->convertToScopes($scope),
			'state' => $state
		]);
	}

	/**
	 * Invoked when POST /authorize is requested.
	 *
	 * @param string|null $client_id
	 * @param string|null $redirect_uri
	 * @param string|null $response_type
	 * @param string|null $scope
	 * @param string|null $state
	 *
	 * @throws \Columba\OAuth2\Exception\OAuthException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function onPostOAuth2Authorize (?string $client_id = null, ?string $redirect_uri = null, ?string $response_type = null, ?string $scope = null, ?string $state = null): void
	{
		$this->oAuth2->validateAuthorizeRequest($client_id, $this->getOwnerId(), $response_type, $redirect_uri, $scope, $client);
		$this->oAuth2->handleAuthorizeRequest($client_id, $this->getOwnerId(), $response_type, $redirect_uri, $scope, $state, isset($_POST['authorize']));
	}

	/**
	 * Invoked when POST /token is requested.
	 *
	 * @return array
	 * @throws \Columba\OAuth2\Exception\OAuthException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 * @internal
	 */
	public final function onPostOAuth2Token (): array
	{
		$this->oAuth2->validateTokenRequest();

		return $this->oAuth2->handleTokenRequest();
	}

	/**
	 * Gets the current owner id.
	 *
	 * @return int|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected abstract function getOwnerId (): ?int;

	/**
	 * Renders the authorize view.
	 *
	 * @param array $context
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected abstract function renderAuthorize (array $context): string;

}
