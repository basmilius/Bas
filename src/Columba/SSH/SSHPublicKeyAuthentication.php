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

namespace Columba\SSH;

use function ssh2_auth_pubkey_file;

/**
 * Class SSHPublicKeyAuthentication
 *
 * @package Columba\SSH
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.0
 */
final class SSHPublicKeyAuthentication extends SSHAuthentication
{

	private string $username;
	private string $publicKeyFile;
	private string $privateKeyFile;
	private string $passphrase;

	/**
	 * SSHPublicKeyAuthentication constructor.
	 *
	 * @param string $username
	 * @param string $publicKeyFile
	 * @param string $privateKeyFile
	 * @param string $passphrase
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(string $username, string $publicKeyFile, string $privateKeyFile, string $passphrase)
	{
		$this->username = $username;
		$this->publicKeyFile = $publicKeyFile;
		$this->privateKeyFile = $privateKeyFile;
		$this->passphrase = $passphrase;
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
		ssh2_auth_pubkey_file($ssh->getResource(), $this->username, $this->publicKeyFile, $this->privateKeyFile, $this->passphrase);
	}

}
