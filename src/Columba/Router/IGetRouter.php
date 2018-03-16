<?php
/**
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Router;

/**
 * Interface IGetRouter
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router
 * @since 1.2.0
 */
interface IGetRouter
{

	/**
	 * Gets the {@see Router} instance.
	 *
	 * @return Router
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	function getRouter (): Router;

}
