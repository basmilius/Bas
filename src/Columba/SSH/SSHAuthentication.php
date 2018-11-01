<?php
declare(strict_types=1);

namespace Columba\SSH;

/**
 * Class SSHAuthentication
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\SSH
 * @since 1.0.0
 */
abstract class SSHAuthentication
{

	/**
	 * Authenticates to SSH.
	 *
	 * @param SSHConnection $ssh
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public abstract function authenticate(SSHConnection $ssh): void;

}
