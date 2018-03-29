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

namespace Columba\OAuth2\ResponseType;

use Columba\OAuth2\Client\Client;

/**
 * Interface IResponseType
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\OAuth2\ResponseType
 * @since 1.3.0
 */
interface IResponseType
{

	/**
	 * Handles the /authorize response.
	 *
	 * @param Client      $client
	 * @param int         $ownerId
	 * @param string      $redirectUri
	 * @param string      $scope
	 * @param string|null $state
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	function handleAuthorizeRequest(Client $client, int $ownerId, string $redirectUri, string $scope, ?string $state): void;

}
