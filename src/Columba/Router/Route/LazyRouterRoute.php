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

namespace Columba\Router\Route;

use Columba\Router\Router;
use Columba\Router\SubRouter;

/**
 * Class LazyRouterRoute
 *
 * @package Columba\Router\Route
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.0
 */
final class LazyRouterRoute extends AbstractRouterRoute
{

	private array $routerArguments;
	private string $routerImplementation;

	/**
	 * LazyRouterRoute constructor.
	 *
	 * @param Router   $parent
	 * @param string[] $requestMethods
	 * @param string   $path
	 * @param string   $routerImplementation
	 * @param mixed    ...$routerArguments
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(Router $parent, array $requestMethods, string $path, string $routerImplementation, ...$routerArguments)
	{
		parent::__construct($parent, $requestMethods, $path);

		$this->routerArguments = $routerArguments;
		$this->routerImplementation = $routerImplementation;

		$this->allowSubRoutes();
	}

	/**
	 * Ensures that a router instance is available.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function ensureRouterInstance(): void
	{
		if ($this->router !== null)
			return;

		$this->router = new $this->routerImplementation(...$this->routerArguments);
		$this->router->setParent($this->getParentRouter());
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
	 * @since 1.3.0
	 */
	public function isMatch(string $path, string $requestMethod): bool
	{
		$this->ensureRouterInstance();

		return parent::isMatch($path, $requestMethod);
	}

}
