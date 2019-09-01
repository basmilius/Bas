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

namespace Columba\Facade;

/**
 * Trait GetHasSetUnset
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Facade
 * @since 1.5.0
 */
trait GetHasSetUnset
{

	/**
	 * Gets a value by key.
	 *
	 * @param mixed $key
	 * @param mixed $defaultValue
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function get($key, $defaultValue = null)
	{
		return $this[$key] ?? $defaultValue;
	}

	/**
	 * Returns TRUE if a key exists.
	 *
	 * @param mixed $key
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function has($key): bool
	{
		return isset($this[$key]);
	}

	/**
	 * Sets a value by key.
	 *
	 * @param mixed $key
	 * @param mixed $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function set($key, $value): void
	{
		$this[$key] = $value;
	}

	/**
	 * Unsets a value by key.
	 *
	 * @param mixed $key
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function unset($key): void
	{
		unset($this[$key]);
	}

}
