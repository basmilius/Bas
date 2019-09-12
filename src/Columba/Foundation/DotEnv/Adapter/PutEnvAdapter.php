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

use Columba\Foundation\DotEnv\DotEnv;

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
	public final function adapt(DotEnv $env): void
	{
		foreach ($env as $name => $value)
		{
			$_ENV[$name] = $value;
			putenv($name . '=' . $value);
		}
	}

}
