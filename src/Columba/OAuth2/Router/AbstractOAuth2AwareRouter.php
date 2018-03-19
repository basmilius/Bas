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

use Columba\OAuth2\Exception\OAuthException;
use Columba\OAuth2\OAuth2;
use Columba\Router\Exception\AccessDeniedException;
use Columba\Router\Renderer\AbstractRenderer;
use Columba\Router\Response\AbstractResponse;
use Columba\Router\Router;

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
	 * @var bool
	 */
	private $isOAuth2Request = false;

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
		$this->checkRequest();

		parent::handle($requestPath, $params, $isSubRoute);
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
	 * Throws an AccessDeniedException if {@see $scope} is not permitted for this request.
	 *
	 * @param string $scope
	 *
	 * @throws AccessDeniedException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected final function assertScope (string $scope): void
	{
		if ($this->validateScope($scope))
			return;

		throw new AccessDeniedException($scope);
	}

	/**
	 * Returns TRUE if a scope is permitted.
	 *
	 * @param string $scope
	 *
	 * @return bool
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.3.0
	 */
	protected final function validateScope (string $scope): bool
	{
		foreach ($this->scopes as $scp)
			if ($scp['scope'] === $scope)
				return true;

		return false;
	}

	/**
	 * Checks the request for oAuth2 params.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	private function checkRequest ()
	{
		if (isset($_SERVER['HTTP_AUTHORIZATION']) && !empty($_SERVER['HTTP_AUTHORIZATION']))
		{
			[$ownerId, $scopes] = $this->oAuth2->validateResource();

			$this->isOAuth2Request = true;
			$this->scopes = $this->filterScopes($ownerId, $scopes);
		}
	}

	/**
	 * Filters scopes based on owner.
	 *
	 * @param int   $ownerId
	 * @param array $scopes
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected abstract function filterScopes (int $ownerId, array $scopes): array;

}
