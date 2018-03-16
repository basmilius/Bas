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

namespace Columba\OAuth2\Client;

use Columba\OAuth2\OAuth2;

/**
 * Interface IClientFactory
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\OAuth2\Client
 * @since 1.3.0
 */
interface IClientFactory
{

	/**
	 * Gets a client by client_id.
	 *
	 * @param string $clientId
	 *
	 * @return Client|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	function getClient (string $clientId): ?Client;

	/**
	 * Gets the redirect uris for a {@see Client} by client_id.
	 *
	 * @param string $clientId
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	function getClientRedirectUris (string $clientId): array;

	/**
	 * Sets the OAuth2 instance.
	 *
	 * @param OAuth2 $oAuth2
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	function setOAuth2 (OAuth2 $oAuth2): void;

}
