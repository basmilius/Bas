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

namespace Columba\Foundation\DotEnv\Adapter;

/**
 * Interface IAdapter
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Foundation\DotEnv\Adapter
 * @since 1.6.0
 */
interface IAdapter
{

	/**
	 * Gets an environment variable.
	 *
	 * @param string $name
	 *
	 * @return string|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function get(string $name): ?string;

	/**
	 * Returns TRUE if an environment variable exists.
	 *
	 * @param string $name
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function has(string $name): bool;

	/**
	 * Adds an environment variable.
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function set(string $name, string $value): void;

}
