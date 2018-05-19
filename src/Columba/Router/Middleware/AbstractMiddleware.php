<?php
declare(strict_types=1);

namespace Columba\Router\Middleware;

use Columba\Router\Route\AbstractRoute;
use Columba\Router\RouteContext;
use Columba\Router\Router;
use Columba\Router\RouterException;

/**
 * Class AbstractRoute
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router\Middleware
 * @since 1.3.0
 */
abstract class AbstractMiddleware
{

	/**
	 * @var Router
	 */
	private $router;

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
	 * @param RouteContext  $context
	 * @param bool          $isRouteValid
	 * @param bool          $isRequestMethodValid
	 *
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public abstract function forContext(AbstractRoute $route, RouteContext $context, bool &$isRouteValid, bool &$isRequestMethodValid): void;

	/**
	 * Gets the associated {@see Router}.
	 *
	 * @return Router
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getRouter(): Router
	{
		return $this->router;
	}

}
