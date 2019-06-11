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

namespace Columba\Foundation\Net;

/**
 * Class IP
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Foundation\Net
 * @since 1.6.0
 */
class IP
{

	public const V4 = 1;
	public const V6 = 2;

	/**
	 * @var string
	 */
	protected $ip;

	/**
	 * @var int
	 */
	protected $version;

	/**
	 * IP constructor.
	 *
	 * @param string $ip
	 * @param int    $version
	 *
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.6.0
	 */
	protected function __construct(string $ip, int $version)
	{
		$this->ip = $ip;
		$this->version = $version;
	}

	/**
	 * Gets the IP.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function getIP(): string
	{
		return $this->ip;
	}

	/**
	 * Gets the IP version.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function getVersion(): int
	{
		return $this->version;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function __toString(): string
	{
		return $this->ip;
	}

	/**
	 * Returns TRUE if {@see $ip} is a valid IPv4 address.
	 *
	 * @param string $ip
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function isV4(string $ip): bool
	{
		return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
	}

	/**
	 * Returns TRUE if {@see $ip} is a valid IPv6 address.
	 *
	 * @param string $ip
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function isV6(string $ip): bool
	{
		return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
	}

	/**
	 * Returns TRUE if {@see $ip} is an IP address.
	 *
	 * @param string $ip
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function isValid(string $ip): bool
	{
		return filter_var($ip, FILTER_VALIDATE_IP) !== false;
	}

	/**
	 * Parses an IP.
	 *
	 * @param string $ip
	 * @param bool   $throw
	 *
	 * @return IP|null
	 * @throws IPException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function parse(string $ip, bool $throw = false): ?self
	{
		if (!self::isValid($ip))
		{
			if ($throw)
				throw new IPException('Not a valid IP address.', IPException::ERR_INVALID_IP);
			else
				return null;
		}

		return new static($ip, self::isV4($ip) ? self::V4 : self::V6);
	}

}
