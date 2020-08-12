<?php
/**
 * Copyright (c) 2019 - 2020 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\OAuth2\GrantType;

use Columba\OAuth2\Exception\OAuth2Exception;

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
	 * @throws OAuth2Exception
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	function handleTokenRequest(string $grantType): array;

}
