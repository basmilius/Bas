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

namespace Columba\Foundation;

/**
 * Class Store
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Foundation
 * @since 1.6.0
 */
class Store
{

	/**
	 * @var array
	 */
	private $data = [];

	/**
	 * Gets an item or returns {@see $defaultValue} when it's not stored.
	 *
	 * @param string $key
	 * @param mixed  $defaultValue
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function get(string $key, $defaultValue)
	{
		return $this->data[$key] ?? $defaultValue;
	}

	/**
	 * Returns an item or creates it when it's not stored.
	 *
	 * @param string   $key
	 * @param callable $creator
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function getOrCreate(string $key, callable $creator)
	{
		if (isset($this->data[$key]))
			return $this->data[$key];

		return $this->data[$key] = $creator();
	}

	/**
	 * Returns TRUE if an item is stored.
	 *
	 * @param string $key
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function has(string $key): bool
	{
		return isset($this->data[$key]);
	}

	/**
	 * Sets an item.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function set(string $key, $value): void
	{
		$this->data[$key] = $value;
	}

}
