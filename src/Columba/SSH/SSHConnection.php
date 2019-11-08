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

use function fgets;
use function flush;
use function header;
use function json_encode;
use function ob_end_flush;
use function ob_flush;
use function ob_start;
use function ssh2_connect;
use function ssh2_disconnect;
use function ssh2_exec;
use function ssh2_scp_recv;
use function ssh2_scp_send;
use function str_pad;
use function stream_get_contents;
use function stream_set_blocking;

/**
 * Class SSHConnection
 *
 * @package Columba\SSH
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.0
 */
final class SSHConnection
{

	/**
	 * @var string
	 */
	private $hostname;

	/**
	 * @var int
	 */
	private $port;

	/**
	 * @var resource
	 */
	private $ssh;

	/**
	 * @var bool
	 */
	private $authenticated = false;

	/**
	 * @var SSHAuthentication
	 */
	private $authentication;

	/**
	 * SSHConnection constructor.
	 *
	 * @param string $hostname
	 * @param int    $port
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(string $hostname, int $port = 22)
	{
		$this->hostname = $hostname;
		$this->port = $port;
		$this->ssh = ssh2_connect($this->hostname, $this->port);
	}

	/**
	 * Disconnects the ssh connection.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function __destruct()
	{
		ssh2_disconnect($this->ssh);
	}

	/**
	 * Gets the SSH resource.
	 *
	 * @return resource
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getResource()
	{
		return $this->ssh;
	}

	/**
	 * Sets the authentication method.
	 *
	 * @param SSHAuthentication $authentication
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function setAuthentication(SSHAuthentication $authentication): void
	{
		$this->authentication = $authentication;
		$this->authentication->authenticate($this);
		$this->authenticated = true;
	}

	/**
	 * Executes a command and return output.
	 *
	 * @param string $command
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function exec(string $command): string
	{
		$stream = ssh2_exec($this->ssh, $command);
		stream_set_blocking($stream, true);

		return stream_get_contents($stream);
	}

	/**
	 * Executes a command and streams the output to php://output.
	 *
	 * @param string        $command
	 * @param callable|null $before
	 * @param callable|null $after
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function execAndStream(string $command, ?callable $before = null, ?callable $after = null)
	{
		header('Content-Type: text/event-stream');
		header('Cache-Control: no-cache');

		ob_start();

		$ansi = new AnsiToHtmlConverter();

		$stream = ssh2_exec($this->ssh, $command);
		stream_set_blocking($stream, true);

		if ($before !== null)
			$before($this);

		while ($line = fgets($stream))
			$this->sendToBrowser('id: output' . PHP_EOL . 'data: ' . json_encode(['line' => $ansi->convert($line)]));

		if ($after !== null)
			$after($this);

		$this->sendToBrowser('id: done' . PHP_EOL . 'data: {}');

		ob_end_flush();
	}

	/**
	 * Receive a file.
	 *
	 * @param string $filename
	 * @param string $localFilename
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function receiveFile(string $filename, string $localFilename): void
	{
		ssh2_scp_recv($this->ssh, $filename, $localFilename);
	}

	/**
	 * Sends a file.
	 *
	 * @param string   $filename
	 * @param string   $remoteFilename
	 * @param int|null $createMode
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function sendFile(string $filename, string $remoteFilename, ?int $createMode = null): void
	{
		ssh2_scp_send($this->ssh, $filename, $remoteFilename, $createMode);
	}

	/**
	 * Sends text to browser, padded with 4096 bytes.
	 *
	 * @param string $text
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function sendToBrowser(string $text): void
	{
		echo str_pad($text, 4096 * 2, ' '), PHP_EOL, PHP_EOL;

		ob_flush();
		flush();
	}

}
