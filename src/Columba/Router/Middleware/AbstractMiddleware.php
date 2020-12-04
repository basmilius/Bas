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

namespace Columba\Router\Middleware;

use Columba\Facade\Debuggable;
use Columba\Router\Context;
use Columba\Router\Route\AbstractRoute;
use Columba\Router\Router;
use Columba\Router\RouterException;

/**
 * Class AbstractRoute
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router\Middleware
 * @since 1.3.0
 */
abstract class AbstractMiddleware implements Debuggable
{

	protected Router $router;

	/**
	 * AbstractRoute constructor.
	 *
	 * @param Router $router
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(Router $router)
	{
		$this->router = $router;
	}

	/**
	 * Performs our this {@see Middlware} for a {@see $route} {@see $context}.
	 *
	 * @param AbstractRoute $route
	 * @param Context $context
	 * @param bool $isValid
	 *
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public abstract function forContext(AbstractRoute $route, Context $context, bool &$isValid): void;

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __debugInfo(): array
	{
		return [];
	}

}
