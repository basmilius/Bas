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

namespace Columba\OAuth2\Client;

use Columba\OAuth2\OAuth2;
use Columba\OAuth2\OAuth2Object;

/**
 * Class Client
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\OAuth2\Client
 * @since 1.3.0
 */
final class Client extends OAuth2Object
{

	/**
	 * Client constructor.
	 *
	 * @param array  $data
	 * @param OAuth2 $oAuth2
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct (array $data, OAuth2 $oAuth2)
	{
		parent::__construct($data, $oAuth2);

		$this->data['redirect_uris'] = $this->oAuth2->getClientFactory()->getClientRedirectUris($this['client_id']);
	}

	/**
	 * Returns TRUE if the redirect uri is valid.
	 *
	 * @param string $redirectUri
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function isValidRedirectUri (string $redirectUri): bool
	{
		return in_array($redirectUri, $this->data['redirect_uris']);
	}

	/**
	 * Returns TRUE if {@see $secret} is the valid client_secret.
	 *
	 * @param string $secret
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function isValidSecret (string $secret): bool
	{
		return $this['client_secret'] === $secret;
	}

}
