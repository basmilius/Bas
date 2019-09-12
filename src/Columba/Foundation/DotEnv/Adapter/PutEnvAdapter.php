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
 * Class PutEnvAdapter
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Foundation\DotEnv\Adapter
 * @since 1.6.0
 */
class PutEnvAdapter implements IAdapter
{

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function get(string $name): ?string
	{
		return getenv($name) ?: null;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function has(string $name): bool
	{
		return getenv($name) !== false;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function set(string $name, string $value): void
	{
		putenv($name . '=' . $value);
	}

}
