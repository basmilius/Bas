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

namespace Columba\Foundation\Http;

use Columba\Facade\Stringable;
use function gmdate;
use function header;
use function implode;
use function time;
use function urlencode;

/**
 * Class Cookie
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Foundation\Http
 * @since 1.6.0
 */
class Cookie implements Stringable
{

	public const LAX = 'Lax';
	public const STRICT = 'Strict';

	protected int $expires;
	protected string $name;
	protected string $value;
	protected ?string $domain;
	protected string $path;
	protected bool $isHttpOnly = true;
	protected bool $isSecure = true;
	protected string $sameSite = self::STRICT;

	/**
	 * Cookie constructor.
	 *
	 * @param string $name
	 * @param string $value
	 * @param int $expires
	 * @param string|null $path
	 * @param string|null $domain
	 * @param bool $isSecure
	 * @param bool $isHttpOnly
	 * @param string $sameSite
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(string $name, string $value, int $expires = 0, ?string $path = null, ?string $domain = null, bool $isSecure = true, bool $isHttpOnly = true, string $sameSite = self::LAX)
	{
		$this->name = $name;
		$this->value = $value;
		$this->expires = $expires;
		$this->domain = $domain;
		$this->path = $path ?? '/';
		$this->isHttpOnly = $isHttpOnly;
		$this->isSecure = $isSecure;
		$this->sameSite = $sameSite;
	}

	/**
	 * Gets when the cookie expires.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getExpires(): int
	{
		return $this->expires;
	}

	/**
	 * Gets the name of the cookie.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Gets the value of the cookie.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getValue(): string
	{
		return $this->value;
	}

	/**
	 * Gets the domain the cookie is available on.
	 *
	 * @return string|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getDomain(): ?string
	{
		return $this->domain;
	}

	/**
	 * Gets the path the cookie is available on.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getPath(): string
	{
		return $this->path;
	}

	/**
	 * Gets if the cookie is only accessible through the HTTP protocol.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getIsHttpOnly(): bool
	{
		return $this->isHttpOnly;
	}

	/**
	 * Gets if the cookie may only be transmitted over a HTTPS connection.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getIsSecure(): bool
	{
		return $this->isSecure;
	}

	/**
	 * Gets the SameSite cookie flag.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getSameSite(): string
	{
		return $this->sameSite;
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __toString(): string
	{
		$cookieParts = [];

		if (empty($this->value))
		{
			$cookieParts[] = urlencode($this->name) . '=trashed';
			$cookieParts[] = 'Expires=' . gmdate('D, d-M-Y H:i:s T', time() - 1);
		}
		else
		{
			$cookieParts[] = urlencode($this->name) . '=' . urlencode($this->value);

			if ($this->expires !== 0)
			{
				$cookieParts[] = 'Max-Age=' . ($this->expires - time());
				$cookieParts[] = 'Expires=' . gmdate('D, d-M-Y H:i:s T', $this->expires);
			}
		}

		$cookieParts[] = 'Path=' . $this->path;

		if ($this->domain !== null)
			$cookieParts[] = 'Domain=' . $this->domain;

		if ($this->isSecure)
			$cookieParts[] = 'Secure';

		if ($this->isHttpOnly)
			$cookieParts[] = 'HttpOnly';

		$cookieParts[] = 'SameSite=' . $this->sameSite;

		return implode('; ', $cookieParts);
	}

	/**
	 * Gets the value of the given cookie name, or NULL of it does not exist.
	 *
	 * @param string $name
	 *
	 * @return string|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function get(string $name): ?string
	{
		return $_COOKIE[$name] ?? null;
	}

	/**
	 * Removes a cookie with the given name.
	 *
	 * @param string $name
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function remove(string $name): void
	{
		header((string)(new self($name, '')));
	}

	/**
	 * Sets a cookie.
	 *
	 * @param Cookie $cookie
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function set(Cookie $cookie): void
	{
		header('Set-Cookie: ' . (string)$cookie, false);
	}

}
