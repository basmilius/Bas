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

namespace Columba\OAuth2\Token;

use Columba\OAuth2\OAuth2;
use Columba\OAuth2\OAuth2Object;
use function time;

/**
 * Class Token
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\OAuth2\Token
 * @since 1.3.0
 */
final class Token extends OAuth2Object
{

	/**
	 * Token constructor.
	 *
	 * @param array $data
	 * @param OAuth2 $oAuth2
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(array $data, OAuth2 $oAuth2)
	{
		parent::__construct($data, $oAuth2);
	}

	/**
	 * Returns TRUE if the token has expired.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function isExpired(): bool
	{
		return $this['expires_at'] !== -1 && $this['expires_at'] < time();
	}

}
