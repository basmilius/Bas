<?php
declare(strict_types=1);

namespace Columba\Router;

use Columba\Http\RequestMethod;
use Columba\Router\Middleware\AbstractMiddleware;
use Columba\Router\Renderer\AbstractRenderer;
use Columba\Router\Response\AbstractResponse;
use Columba\Router\Response\ResponseWrapper;
use Columba\Router\Route\AbstractRoute;
use Columba\Router\Route\CallbackRoute;
use Columba\Router\Route\LazyRouterRoute;
use Columba\Router\Route\RouterRoute;
use Exception;

/**
 * Class Router
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router
 * @since 1.3.0
 */
class Router
{

	/**
	 * @var AbstractMiddleware[]
	 */
	private $middlewares;

	/**
	 * @var AbstractRenderer
	 */
	private $renderer;

	/**
	 * @var AbstractResponse
	 */
	private $response;

	/**
	 * @var AbstractRoute[]
	 */
	private $routes;

	/**
	 * Router constructor.
	 *
	 * @param AbstractResponse|null $response
	 * @param AbstractRenderer|null $renderer
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(?AbstractResponse $response = null, ?AbstractRenderer $renderer = null)
	{
		$this->middlewares = [];
		$this->renderer = $renderer;
		$this->response = $response;
		$this->routes = [];
	}

	/**
	 * Adds a {@see AbstractRoute}.
	 *
	 * @param AbstractRoute $route
	 *
	 * @return Router
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function add(AbstractRoute $route): Router
	{
		array_unshift($this->routes, $route);

		return $this;
	}

	/**
	 * Tries to guess the {@see AbstractRoute} instance and adds it.
	 *
	 * @param string $path
	 * @param mixed  ...$arguments
	 *
	 * @return AbstractRoute
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since
	 */
	public final function addFromArguments(string $path, ...$arguments): AbstractRoute
	{
		$route = null;

		if (count($arguments) > 0 && is_array($arguments[0]) && is_callable($arguments[0]))
			$route = new CallbackRoute($this, $path, ...$arguments);

		if (count($arguments) > 0 && $arguments[0] instanceof Router)
			$route = new RouterRoute($this, $path, ...$arguments);

		if (count($arguments) > 0 && is_string($arguments[0]) && is_subclass_of($arguments[0], Router::class))
			$route = new LazyRouterRoute($this, $path, ...$arguments);

		if (count($arguments) > 0 && $arguments[0] instanceof IGetRouter)
			$route = new RouterRoute($this, $path, $arguments[0]->getRouter());

		if ($route === null)
			throw new RouterException('Could not determine route implementation', RouterException::ERR_NO_ROUTE_IMPLEMENTATION);

		$this->add($route);

		return $route;
	}

	/**
	 * Gets all enabled middlewares for this {@see Router}.
	 *
	 * @return AbstractMiddleware[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getMiddlewares(): array
	{
		return $this->middlewares;
	}

	/**
	 * Adds a {@see AbstractRoute} to use.
	 *
	 * @param string $middleware
	 * @param mixed  ...$arguments
	 *
	 * @return Router
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function use(string $middleware, ...$arguments): Router
	{
		if (!class_exists($middleware))
			throw new RouterException('Middleware ' . $middleware . ' not found!', RouterException::ERR_MIDDLEWARE_NOT_FOUND);

		if (!is_subclass_of($middleware, AbstractMiddleware::class))
			throw new RouterException('Middleware needs to extend from ' . AbstractMiddleware::class, RouterException::ERR_MIDDLEWARE_INVALID);

		$this->middlewares[] = new $middleware($this, ...$arguments);

		return $this;
	}

	/**
	 * Searches for a matching {@see AbstractRoute}.
	 *
	 * @param string            $path
	 * @param string            $requestMethod
	 * @param RouteContext|null $context
	 *
	 * @return AbstractRoute|null
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function find(string $path, string $requestMethod, ?RouteContext $context = null): ?AbstractRoute
	{
		if (empty($path))
			$path = '/';

		foreach ($this->routes as $route)
		{
			if ($context !== null)
				$route->getContext()->setParent($context);

			if ($route->isMatch($path, $requestMethod))
				return $route;
		}

		return null;
	}

	/**
	 * Searches for a matching {@see AbstractRoute} and executes it.
	 *
	 * @param string $path
	 * @param string $requestMethod
	 *
	 * @return mixed
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function execute(string $path, string $requestMethod)
	{
		$route = $this->find($path, $requestMethod);

		if ($route === null)
		{
			$this->onException(new RouterException($path . ' was not found!', RouterException::ERR_NOT_FOUND));
			return null;
		}

		return $route->execute(false);
	}

	/**
	 * Searches for a matching {@see AbstractRoute}, executes it and responds to the output buffer.
	 *
	 * @param string $path
	 * @param string $requestMethod
	 *
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function executeAndRespond(string $path, string $requestMethod): void
	{
		$route = $this->find($path, $requestMethod);

		if ($route === null)
		{
			$this->onException(new RouterException($path . ' was not found!', RouterException::ERR_NOT_FOUND));
			return;
		}

		$route->execute(true);
	}

	/**
	 * Renders a {@see $template} with the given {@see $context} using an {@see AbstractRenderer}.
	 *
	 * @param string $template
	 * @param array  $context
	 *
	 * @return string
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function render(string $template, array $context = []): string
	{
		$renderer = $this->getRenderer();

		if ($renderer === null)
			throw new RouterException('No renderer defined', RouterException::ERR_NULL_RENDERER);

		return $renderer->render($template, $context);
	}

	/**
	 * Sends a custom response.
	 *
	 * @param string $implementation
	 * @param mixed  $value
	 * @param mixed  ...$options
	 *
	 * @return ResponseWrapper
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function respond(string $implementation, $value, ...$options): ResponseWrapper
	{
		if (!is_subclass_of($implementation, AbstractResponse::class))
			throw new RouterException('Invalid response implementation! Needs to extend form AbstractResponse.', 0);

		return new ResponseWrapper(new $implementation(...$options), $value);
	}

	/**
	 * Adds an ALL {@see AbstractRoute}.
	 *
	 * @param string $path
	 * @param mixed  ...$arguments
	 *
	 * @return AbstractRoute
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function all(string $path, ...$arguments): AbstractRoute
	{
		return $this->addFromArguments($path, ...$arguments);
	}

	/**
	 * Adds a DELETE {@see AbstractRoute}.
	 *
	 * @param string $path
	 * @param mixed  ...$arguments
	 *
	 * @return AbstractRoute
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function delete(string $path, ...$arguments): AbstractRoute
	{
		$route = $this->addFromArguments($path, ...$arguments);
		$route->setRequestMethod(RequestMethod::DELETE);

		return $route;
	}

	/**
	 * Adds a GET {@see AbstractRoute}.
	 *
	 * @param string $path
	 * @param mixed  ...$arguments
	 *
	 * @return AbstractRoute
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function get(string $path, ...$arguments): AbstractRoute
	{
		$route = $this->addFromArguments($path, ...$arguments);
		$route->setRequestMethod(RequestMethod::GET);

		return $route;
	}

	/**
	 * Adds a HEAD {@see AbstractRoute}.
	 *
	 * @param string $path
	 * @param mixed  ...$arguments
	 *
	 * @return AbstractRoute
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function head(string $path, ...$arguments): AbstractRoute
	{
		$route = $this->addFromArguments($path, ...$arguments);
		$route->setRequestMethod(RequestMethod::HEAD);

		return $route;
	}

	/**
	 * Adds an OPTIONS {@see AbstractRoute}.
	 *
	 * @param string $path
	 * @param mixed  ...$arguments
	 *
	 * @return AbstractRoute
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function options(string $path, ...$arguments): AbstractRoute
	{
		$route = $this->addFromArguments($path, ...$arguments);
		$route->setRequestMethod(RequestMethod::OPTIONS);

		return $route;
	}

	/**
	 * Adds a PATCH {@see AbstractRoute}.
	 *
	 * @param string $path
	 * @param mixed  ...$arguments
	 *
	 * @return AbstractRoute
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function patch(string $path, ...$arguments): AbstractRoute
	{
		$route = $this->addFromArguments($path, ...$arguments);
		$route->setRequestMethod(RequestMethod::PATCH);

		return $route;
	}

	/**
	 * Adds a POST {@see AbstractRoute}.
	 *
	 * @param string $path
	 * @param mixed  ...$arguments
	 *
	 * @return AbstractRoute
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function post(string $path, ...$arguments): AbstractRoute
	{
		$route = $this->addFromArguments($path, ...$arguments);
		$route->setRequestMethod(RequestMethod::POST);

		return $route;
	}

	/**
	 * Adds a PUT {@see AbstractRoute}.
	 *
	 * @param string $path
	 * @param mixed  ...$arguments
	 *
	 * @return AbstractRoute
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function put(string $path, ...$arguments): AbstractRoute
	{
		$route = $this->addFromArguments($path, ...$arguments);
		$route->setRequestMethod(RequestMethod::PUT);

		return $route;
	}

	/**
	 * Invoked when an {@see Exception} is thrown.
	 *
	 * @param Exception $err
	 *
	 * @return mixed|null
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function onException(Exception $err)
	{
		if ($err instanceof RouterException)
			throw $err;
		else
			throw new RouterException('Route handler threw an exception!', RouterException::ERR_HANDLER_THREW_EXCEPTION, $err);
	}

	/**
	 * Gets the {@see AbstractRenderer}.
	 *
	 * @return AbstractRenderer|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function getRenderer(): ?AbstractRenderer
	{
		return $this->renderer;
	}

	/**
	 * Gets the {@see AbstractResponse}.
	 *
	 * @return AbstractResponse
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function getResponse(): ?AbstractResponse
	{
		return $this->response;
	}

}
