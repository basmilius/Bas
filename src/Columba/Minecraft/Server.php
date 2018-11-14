<?php
declare(strict_types=1);

/**
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace Columba\Minecraft;

use LogicException;

/**
 * Class Server
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Minecraft
 * @since 1.4.0
 */
final class Server
{

	private $host;
	private $port;
	private $timeout;

	private $socket = null;

	public function __construct(string $host, int $port = 25565, int $timeout = 10, bool $resolveService = true)
	{
		$this->host = $host;
		$this->port = $port;
		$this->timeout = $timeout;

		if ($resolveService)
			$this->resolveService();
	}

	public function __destruct()
	{
		$this->close();
	}

	public final function close(): void
	{
		if ($this->socket === null)
			return;

		fclose($this->socket);
		$this->socket = null;
	}

	public final function connect(): void
	{
		$this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);

		if (!$this->socket)
		{
			$this->socket = null;
			throw new LogicException("Failed to connect! $errno ($errstr)");
		}

		socket_set_timeout($this->socket, $this->timeout);
	}

	public final function query(): ?array
	{
		$start = microtime(true);

		/*
		$Data .= Pack( 'c', StrLen( $this->ServerAddress ) ) . $this->ServerAddress; // Server (varint len + UTF-8 addr)
		$Data .= Pack( 'n', $this->ServerPort ); // Server port (unsigned short)
		$Data .= "\x01"; // Next state: status (varint)
		 */

		$data = "\x00";
		$data .= "\x04";
		$data .= pack('c', strlen($this->host)) . $this->host;
		$data .= pack('n', $this->port);
		$data .= "\x01";
		$data = pack('c', strlen($data)) . $data;

		fwrite($this->socket, $data);
		fwrite($this->socket, "\x01\x00");

		$length = $this->readInteger();

		if ($length < 10)
			return null;

		$data = '';

		do
		{
			if (microtime(true) - $start > $this->timeout)
				throw new LogicException('Query timed out!');

			$remainder = $length - strlen($data);
			$block = fread($this->socket, $remainder);

			if (!$block)
				throw new LogicException('Not enough data was returned by the server.');

			$data .= $block;
		}
		while (strlen($data) < $length);

		if ($data === false)
			throw new LogicException('Server didn\'t respond any data.');

		$data = substr($data, 3);
		$json = json_decode($data, true);

		if (json_last_error() === JSON_ERROR_NONE)
			return $json;

		print_r([json_last_error(), json_last_error_msg(), $data]);

		return null;
	}

	private function readInteger(): int
	{
		$i = 0;
		$j = 0;

		while (true)
		{
			$k = @fgetc($this->socket);

			if ($k === false)
				return 0;

			$k = ord($k);
			$i |= ($k & 0x7F) << $j++ * 7;

			if ($j > 5)
				throw new LogicException('Integer too big!');

			if (($k & 0x80) !== 128)
				break;
		}

		return $i;
	}

	private function resolveService(): void
	{
		if (ip2long($this->host) !== false)
			return;

		$record = dns_get_record('_minecraft._tcp.' . $this->host, DNS_SRV);

		if (empty($record))
			return;

		if (isset($record[0]['target']))
			$this->host = $record[0]['target'];

		if (isset($record[0]['port']))
			$this->port = $record[0]['port'];
	}

}
