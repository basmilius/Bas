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

namespace Columba\OAuth2\Scope;

use Columba\OAuth2\Exception\InvalidScopeException;
use Columba\OAuth2\OAuth2;

/**
 * Interface IScopeFactory
 *
 * @package Columba\OAuth2\Scope
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.0
 */
interface IScopeFactory
{

	/**
	 * Converts the scope parameter in request to an array with scopes.
	 *
	 * @param string $scope
	 *
	 * @return array
	 * @throws InvalidScopeException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	function convertToScopes(string $scope): array;

	/**
	 * Gets details about a scope. Should return an array with scope, name and description.
	 *
	 * @param string $scope
	 *
	 * @return array|null
	 * @throws InvalidScopeException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	function getScope(string $scope): ?array;

	/**
	 * Gets a list with available scopes.
	 *
	 * @return string[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	function getScopes(): array;

	/**
	 * Validates if a {@see $scope} is valid for {@see $ownerId}.
	 *
	 * @param int    $ownerId
	 * @param string $scope
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	function isScopeAllowed(int $ownerId, string $scope): bool;

	/**
	 * Sets the OAuth2 instance.
	 *
	 * @param OAuth2 $oAuth2
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	function setOAuth2(OAuth2 $oAuth2): void;

}
