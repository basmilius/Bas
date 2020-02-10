<?php
/**
 * Copyright (c) 2019 - 2020 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Router\Route;

use Columba\Router\Router;
use Columba\Router\SubRouter;

/**
 * Class RouterRoute
 *
 * @package Columba\Router\Route
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.0
 */
final class RouterRoute extends AbstractRouterRoute
{

	/**
	 * RouterRoute constructor.
	 *
	 * @param Router    $parent
	 * @param string[]  $requestMethods
	 * @param string    $path
	 * @param SubRouter $router
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(Router $parent, array $requestMethods, string $path, SubRouter $router)
	{
		parent::__construct($parent, $requestMethods, $path);

		$this->router = $router;
		$this->router->setParent($parent);

		$this->allowSubRoutes();
	}

	/**
	 * Gets the {@see SubRouter} instance.
	 *
	 * @return SubRouter
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getRouter(): SubRouter
	{
		return $this->router;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getValidatableParams(): array
	{
		if ($this->router instanceof SubRouter)
			return $this->router->getParameters();

		return [];
	}

}
