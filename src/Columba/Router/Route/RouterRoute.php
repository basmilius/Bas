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
use Columba\Router\RouterException;
use Columba\Router\SubRouter;

/**
 * Class RouterRoute
 *
 * @package Columba\Router\Route
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.0
 */
final class RouterRoute extends AbstractRoute
{

	/**
	 * @var SubRouter
	 */
	private $router;

	/**
	 * @var AbstractRoute|null
	 */
	private $matchingRoute = null;

	/**
	 * RouterRoute constructor.
	 *
	 * @param Router    $parent
	 * @param string    $path
	 * @param SubRouter $router
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(Router $parent, string $path, SubRouter $router)
	{
		parent::__construct($parent, $path);

		$this->router = $router;
		$this->router->setParent($parent);

		$this->setAllowSubRoutes(true);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function executeImpl(): void
	{
		if ($this->matchingRoute === null)
			throw new RouterException('Illegal call, matchingRoute is NULL', RouterException::ERR_ILLEGAL);

		$this->matchingRoute->execute();
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
		$isMatch = parent::isMatch($path, $requestMethod);

		if (!$isMatch)
			return false;

		$relativePath = mb_substr($path, mb_strlen($this->getContext()->getPathValues()));

		if (empty($relativePath))
			$relativePath = '/';

		if (mb_substr($relativePath, 0, 1) !== '/')
			$relativePath = '/' . $relativePath;

		return ($this->matchingRoute = $this->router->find($relativePath, $requestMethod, $this->getContext())) !== null;
	}

}
