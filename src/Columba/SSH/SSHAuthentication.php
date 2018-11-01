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

namespace Columba\SSH;

/**
 * Class SSHAuthentication
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\SSH
 * @since 1.3.0
 */
abstract class SSHAuthentication
{

	/**
	 * Authenticates to SSH.
	 *
	 * @param SSHConnection $ssh
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public abstract function authenticate(SSHConnection $ssh): void;

}
