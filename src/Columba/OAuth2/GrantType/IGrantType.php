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

namespace Columba\OAuth2\GrantType;

use Columba\OAuth2\Exception\OAuthException;

/**
 * Interface IGrantType
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\OAuth2\GrantType
 * @since 1.3.0
 */
interface IGrantType
{

	/**
	 * Handles the token request.
	 *
	 * @param string $grantType
	 *
	 * @return array
	 * @throws OAuthException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	function handleTokenRequest (string $grantType): array;

}
