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
 * Class LazyRouterRoute
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router\Route
 * @since 1.3.0
 */
final class LazyRouterRoute extends AbstractRouterRoute
{

	private array $routerArguments;
	private string $routerImplementation;

	/**
	 * LazyRouterRoute constructor.
	 *
	 * @param Router $parent
	 * @param string[] $requestMethods
	 * @param string $path
	 * @param string $routerImplementation
	 * @param mixed ...$routerArguments
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(Router $parent, array $requestMethods, string $path, string $routerImplementation, mixed ...$routerArguments)
	{
		parent::__construct($parent, $requestMethods, $path);

		$this->routerArguments = $routerArguments;
		$this->routerImplementation = $routerImplementation;

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
		$this->ensureRouterInstance();

		return $this->router;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getValidatableParams(): array
	{
		$this->ensureRouterInstance();

		if ($this->router instanceof SubRouter)
			return $this->router->getParameters();

		return [];
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function ensureRouterInstance(): void
	{
		if ($this->router !== null)
			return;

		$this->router = new $this->routerImplementation(...$this->routerArguments);
		$this->router->setParent($this->getParentRouter());
	}

}
