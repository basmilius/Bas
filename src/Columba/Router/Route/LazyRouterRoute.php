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

namespace Columba\Router\Route;

use Columba\Router\Router;
use Columba\Router\RouterException;
use Columba\Router\SubRouter;

/**
 * Class LazyRouterRoute
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router\Route
 * @since 1.3.0
 */
final class LazyRouterRoute extends AbstractRoute
{

	/**
	 * @var SubRouter|string
	 */
	private $router;

	/**
	 * @var mixed[]
	 */
	private $routerArguments;

	/**
	 * @var string
	 */
	private $routerImplementation;

	/**
	 * @var AbstractRoute|null
	 */
	private $matchingRoute = null;

	/**
	 * LazyRouterRoute constructor.
	 *
	 * @param Router $parent
	 * @param string $path
	 * @param string $routerImplementation
	 * @param mixed  ...$routerArguments
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(Router $parent, string $path, string $routerImplementation, ...$routerArguments)
	{
		parent::__construct($parent, $path);

		$this->routerArguments = $routerArguments;
		$this->routerImplementation = $routerImplementation;

		$this->setAllowSubRoutes(true);
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
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function executeImpl(bool $respond)
	{
		if ($this->matchingRoute === null)
			throw new RouterException('Illegal call, matchingRoute is NULL');

		return $this->matchingRoute->execute($respond);
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

		$params = [];

		if ($this->router instanceof SubRouter)
			$params = array_merge($params, $this->router->getParameters());

		return $params;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function isMatch(string $path, string $requestMethod): bool
	{
		$this->ensureRouterInstance();

		$isMatch = parent::isMatch($path, $requestMethod);

		if (!$isMatch)
			return false;

		$relativePath = substr($path, mb_strlen($this->getContext()->getPathValues()));

		if (empty($relativePath))
			$relativePath = '/';

		if (substr($relativePath, 0, 1) !== '/')
			$relativePath = '/' . $relativePath;

		return ($this->matchingRoute = $this->router->find($relativePath, $requestMethod, $this->getContext())) !== null;
	}

}
