<?php
declare(strict_types=1);

namespace Columba\Router;

/**
 * Interface IGetRouter
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router
 * @since 1.3.0
 */
interface IGetRouter
{

	/**
	 * Gets the {@see Router} instance.
	 *
	 * @return Router
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function getRouter(): Router;

}
