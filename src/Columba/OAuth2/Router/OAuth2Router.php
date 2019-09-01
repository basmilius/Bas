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

namespace Columba\OAuth2\Router;

use Columba\OAuth2\Exception\InvalidScopeException;
use Columba\OAuth2\Exception\OAuth2Exception;
use Columba\OAuth2\OAuth2;
use Columba\Router\Renderer\AbstractRenderer;
use Columba\Router\Response\AbstractResponse;
use Columba\Router\Response\JsonResponse;
use Columba\Router\Response\ResponseWrapper;
use Columba\Router\Context;
use Columba\Router\RouterException;
use Columba\Router\SubRouter;
use Exception;

/**
 * Class OAuth2Router
 *
 * @package Columba\OAuth2\Router
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.0
 */
abstract class OAuth2Router extends SubRouter
{

	/**
	 * @var OAuth2
	 */
	private $oAuth2;

	/**
	 * OAuth2Router constructor.
	 *
	 * @param OAuth2                $oAuth2
	 * @param AbstractResponse|null $response
	 * @param AbstractRenderer|null $renderer
	 *
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(OAuth2 $oAuth2, ?AbstractResponse $response = null, ?AbstractRenderer $renderer = null)
	{
		$this->oAuth2 = $oAuth2;

		parent::__construct($response, $renderer);

		$this->get('/authorize', [$this, 'onGetAuthorize']);

		$this->post('/authorize', [$this, 'onPostAuthorize']);
		$this->post('/token', [$this, 'onPostToken']);
	}

	/**
	 * Invoked when GET /authorize is requested.
	 *
	 * @param Context     $context
	 * @param string|null $client_id
	 * @param string|null $redirect_uri
	 * @param string|null $response_type
	 * @param string|null $scope
	 * @param string|null $state
	 *
	 * @return string
	 * @throws RouterException
	 * @throws InvalidScopeException
	 * @throws OAuth2Exception
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 * @internal
	 */
	public final function onGetAuthorize(Context $context, ?string $client_id = null, ?string $redirect_uri = null, ?string $response_type = null, ?string $scope = null, ?string $state = null): string
	{
		$ownerId = $this->getOwnerId();

		if ($ownerId === null)
		{
			$this->onOwnerNull($context);
			return '';
		}

		$this->oAuth2->validateAuthorizeRequest($client_id, $ownerId, $response_type, $redirect_uri, $scope, $client);

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
	 * @throws OAuth2Exception
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 * @internal
	 */
	public final function onPostAuthorize(?string $client_id = null, ?string $redirect_uri = null, ?string $response_type = null, ?string $scope = null, ?string $state = null): void
	{
		$this->oAuth2->validateAuthorizeRequest($client_id, $this->getOwnerId(), $response_type, $redirect_uri, $scope, $client);
		$this->oAuth2->handleAuthorizeRequest($client_id, $this->getOwnerId(), $response_type, $redirect_uri, $scope, $state, isset($_POST['authorize']));
	}

	/**
	 * Invoked when POST /token is requested.
	 *
	 * @return ResponseWrapper
	 * @throws OAuth2Exception
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 * @internal
	 */
	public final function onPostToken(): ResponseWrapper
	{
		$this->oAuth2->validateTokenRequest();

		return $this->respond(JsonResponse::class, $this->oAuth2->handleTokenRequest(), false);
	}

	/**
	 * Gets the current owner id.
	 *
	 * @return int|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected abstract function getOwnerId(): ?int;

	/**
	 * Invoked when the owner is NULL.
	 *
	 * @param Context $context
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected abstract function onOwnerNull(Context $context): void;

	/**
	 * Renders the authorize view.
	 *
	 * @param array $context
	 *
	 * @return string
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected abstract function renderAuthorize(array $context): string;

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function onException(Exception $err, ?Context $context = null): void
	{
		if ($err instanceof OAuth2Exception)
		{
			$response = $this->respond(JsonResponse::class, $err, false);
			$response->getResponse()->print($context, $response->getValue());
			die;
		}

		parent::onException($err, $context);
	}

}
