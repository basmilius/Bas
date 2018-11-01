<?php
declare(strict_types=1);

namespace Columba\SSH;

/**
 * Class SSHPasswordAuthentication
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\SSH
 * @since 1.0.0
 */
final class SSHPasswordAuthentication extends SSHAuthentication
{

	/**
	 * @var string
	 */
	private $username;

	/**
	 * @var string
	 */
	private $password;

	/**
	 * SSHPasswordAuthentication constructor.
	 *
	 * @param string $username
	 * @param string $password
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
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
	 * @since 1.0.0
	 */
	public final function authenticate(SSHConnection $ssh): void
	{
		ssh2_auth_password($ssh->getResource(), $this->username, $this->password);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function __debugInfo()
	{
		return [];
	}

}
