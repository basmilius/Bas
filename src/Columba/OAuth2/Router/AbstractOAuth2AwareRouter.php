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

use Columba\OAuth2\Exception\InsufficientClientScopeException;
use Columba\OAuth2\Exception\OAuth2Exception;
use Columba\OAuth2\OAuth2;
use Columba\Router\Renderer\AbstractRenderer;
use Columba\Router\Response\AbstractResponse;
use Columba\Router\Response\JsonResponse;
use Columba\Router\Router;
use JsonSerializable;

/**
 * Class OAuth2AwareRouter
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\OAuth2\Router
 * @since 1.3.0
 */
abstract class AbstractOAuth2AwareRouter extends Router
{

	/**
	 * @var OAuth2
	 */
	protected $oAuth2;

	/**
	 * @var string|null
	 */
	private $authType;

	/**
	 * @var bool
	 */
	private $isOAuth2Request;

	/**
	 * @var int
	 */
	private $ownerId;

	/**
	 * @var array
	 */
	private $scopes;

	/**
	 * OAuth2AwareRouter constructor.
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
		parent::__construct($response, $renderer);

		$this->isOAuth2Request = isset($_SERVER['HTTP_AUTHORIZATION']) && !empty($_SERVER['HTTP_AUTHORIZATION']) && strpos($_SERVER['HTTP_AUTHORIZATION'], ' ');
		$this->authType = $this->isOAuth2Request ? explode(' ', $_SERVER['HTTP_AUTHORIZATION'])[0] : null;

		$this->oAuth2 = $oAuth2;
		$this->scopes = [];
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected function handle (string $requestPath, array $params = [], bool $isSubRoute = false): void
	{
		try
		{
			$this->checkRequest();

			parent::handle($requestPath, $params, $isSubRoute);
		}
		catch (JsonSerializable $err)
		{
			$this->response(new JsonResponse(false))->print($err);
		}
	}

	/**
	 * Returns TRUE if auth type is basic.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected final function isAuthBasic (): bool
	{
		return $this->isOAuth2Request && $this->authType === 'Basic';
	}

	/**
	 * Returns TRUE if auth type is bearer.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected final function isAuthBearer (): bool
	{
		return $this->isOAuth2Request && $this->authType === 'Bearer';
	}

	/**
	 * Returns TRUE if this is an oAuth2 request.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected final function isOAuth2Request (): bool
	{
		return $this->isOAuth2Request;
	}

	/**
	 * Returns TRUE if a {@see $scope} is allowed.
	 *
	 * @param string $scope
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected final function isScopeAllowed (string $scope): bool
	{
		$validInToken = false;

		foreach ($this->scopes as ['scope' => $scp])
			if ($scp === $scope)
				$validInToken = true;

		if (!$validInToken)
			return false;

		return $this->oAuth2->getScopeFactory()->isScopeAllowed($this->ownerId, $scope);
	}

	/**
	 * Validates if {@see $scope} is permitted for the authenticated token.
	 *
	 * @param string $scope
	 *
	 * @throws InsufficientClientScopeException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected final function validateScope (string $scope): void
	{
		if (!$this->isScopeAllowed($scope))
			throw new InsufficientClientScopeException();
	}

	/**
	 * Checks the request for oAuth2 params.
	 *
	 * @throws OAuth2Exception
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	private function checkRequest ()
	{
		if (!$this->isAuthBearer())
			return;

		[$ownerId, $scopes] = $this->oAuth2->validateResource();
		$this->ownerId = $ownerId;
		$this->scopes = $scopes;

		$this->onOwnerIdAvailable($this->ownerId);
	}

	/**
	 * Invoked when a owner id is available.
	 *
	 * @param int $ownerId
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected function onOwnerIdAvailable (int $ownerId): void
	{
	}

}
