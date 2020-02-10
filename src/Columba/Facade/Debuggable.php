<?php
/**
 * Copyright (c) 2017 - 2020 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Latte Framework package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Facade;

/**
 * Interface Debuggable
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Facade
 * @since 1.6.0
 */
interface Debuggable
{

	/**
	 * Returns debug information of the current object.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __debugInfo(): array;

}
