<?php
/**
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Router;

/**
 * Class AbstractRouteController
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router
 * @since 1.0.0
 */
abstract class AbstractRouteController
{

	/**
	 * Invoked after the {@see self::handle()} method.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function onAfterHandle (): void
	{
	}

	/**
	 * Invoked before the {@see self::handle()} method.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function onBeforeHandle (): void
	{
	}

	/**
	 * Handles the request.
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public abstract function handle ();

}