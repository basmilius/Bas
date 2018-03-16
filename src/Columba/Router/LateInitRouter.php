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
 * Class LateInitRouter
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router
 * @since 1.2.0
 */
final class LateInitRouter
{

	/**
	 * @var mixed[]
	 */
	private $arguments;

	/**
	 * @var string
	 */
	private $className;

	/**
	 * @var Router|null
	 */
	private $parent;

	/**
	 * LateInitRouter constructor.
	 *
	 * @param string $className
	 * @param array  $arguments
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public function __construct (string $className, ...$arguments)
	{
		$this->arguments = $arguments;
		$this->className = $className;
		$this->parent = null;
	}

	/**
	 * Sets the parent Router.
	 *
	 * @param Router $router
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function setParent (Router $router): void
	{
		$this->parent = $router;
	}

	/**
	 * Creates the Router instance.
	 *
	 * @return Router
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function createRouter (): Router
	{
		/** @var Router $router */
		$router = new $this->className(...$this->arguments);
		$router->setParent($this->parent);

		return $router;
	}

}
