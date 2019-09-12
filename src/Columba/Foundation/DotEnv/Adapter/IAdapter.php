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
 * Interface IAdapter
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Foundation\DotEnv\Adapter
 * @since 1.6.0
 */
interface IAdapter
{

	/**
	 * Does something with the defined environment variables.
	 *
	 * @param DotEnv $env
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function adapt(DotEnv $env): void;

}
