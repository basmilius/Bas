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
use function mb_strlen;
use function mb_substr;

/**
 * Class AbstractRouterRoute
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router\Route
 * @since 1.6.0
 */
abstract class AbstractRouterRoute extends AbstractRoute
{

	protected ?SubRouter $router = null;
	protected ?AbstractRoute $matchingRoute = null;

	/**
	 * AbstractRouterRoute constructor.
	 *
	 * @param Router $router
	 * @param array  $requestMethods
	 * @param string $path
	 *
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.6.0
	 */
	public function __construct(Router $router, array $requestMethods, string $path)
	{
		parent::__construct($router, $requestMethods, $path);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function executeImpl(): void
	{
		if ($this->matchingRoute !== null)
			$this->matchingRoute->execute();
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function isMatch(string $path, string $requestMethod): bool
	{
		$isMatch = parent::isMatch($path, $requestMethod);

		if (!$isMatch)
			return false;

		$relativePath = mb_substr($path, mb_strlen($this->getContext()->getPathValues()));

		if (empty($relativePath))
			$relativePath = '/';

		if ($relativePath[0] !== '/')
			$relativePath = '/' . $relativePath;

		$this->matchingRoute = $this->router->find($relativePath, $requestMethod, $this->getContext());

		if ($this->matchingRoute === null)
			return $this->router->onNotFound($path, $this->getContext());

		return true;
	}

}
