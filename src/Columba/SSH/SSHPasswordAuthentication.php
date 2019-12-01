<?php
/**
 * Copyright (c) 2019 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\SSH;

use function ssh2_auth_password;

/**
 * Class SSHPasswordAuthentication
 *
 * @package Columba\SSH
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.0
 */
final class SSHPasswordAuthentication extends SSHAuthentication
{

	private string $username;
	private string $password;

	/**
	 * SSHPasswordAuthentication constructor.
	 *
	 * @param string $username
	 * @param string $password
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(string $username, string $password)
	{
		$this->username = $username;
		$this->password = $password;
	}

	/**
	 * Authenticates to SSH.
	 *
	 * @param SSHConnection $ssh
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function authenticate(SSHConnection $ssh): void
	{
		ssh2_auth_password($ssh->getResource(), $this->username, $this->password);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function __debugInfo()
	{
		return [];
	}

}
