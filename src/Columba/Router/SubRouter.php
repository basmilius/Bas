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

namespace Columba\Router;

use Columba\Router\Renderer\AbstractRenderer;
use Columba\Router\Response\AbstractResponse;
use Columba\Router\Response\ResponseMethods;
use function array_merge;

/**
 * Class SubRouter
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router
 * @since 1.3.0
 */
class SubRouter extends Router
{

	use ResponseMethods;

	private Router $parent;

	/** @var RouteParam[] */
	private array $parameters = [];

	/**
	 * Adds a route param.
	 *
	 * @param string $name
	 * @param string $type
	 * @param bool $allowsNull
	 * @param mixed $defaultValue
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function addParam(string $name, string $type, bool $allowsNull = false, $defaultValue = null): void
	{
		$this->parameters[] = new RouteParam($name, $type, $allowsNull, $defaultValue);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function getGlobals(): array
	{
		$globals = parent::getGlobals();

		return array_merge($this->parent->getGlobals(), $globals);
	}

	/**
	 * Gets sub router parameters.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getParameters(): array
	{
		return $this->parameters;
	}

	/**
	 * Gets the root router instance.
	 *
	 * @return Router
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function getRootRouter(): Router
	{
		if ($this->parent instanceof SubRouter)
			return $this->parent->getRootRouter();

		return $this->parent;
	}

	/**
	 * Gets the parent {@see Router}.
	 *
	 * @return Router
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getParent(): Router
	{
		return $this->parent;
	}

	/**
	 * Sets the parent {@see Router}.
	 *
	 * @param Router $parent
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function setParent(Router $parent): void
	{
		$this->parent = $parent;
	}

	/**
	 * Invoked when a route is not found in the current router. Return TRUE if you want
	 * to handle the request yourself. Note that eventual other subrouters with the same
	 * path will not be executed when you return TRUE.
	 *
	 * @param string $requestPath
	 * @param Context $context
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onNotFound(string $requestPath, Context $context): bool
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function getRenderer(): ?AbstractRenderer
	{
		$renderer = parent::getRenderer();

		if ($renderer !== null)
			return $renderer;

		if ($this->parent !== null)
			return $this->parent->getRenderer();

		return null;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function getResponse(): ?AbstractResponse
	{
		$response = parent::getResponse();

		if ($response !== null)
			return $response;

		if ($this->parent !== null)
			return $this->parent->getResponse();

		return null;
	}

}
