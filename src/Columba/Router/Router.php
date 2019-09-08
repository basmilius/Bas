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

namespace Columba\Router;

use Closure;
use Columba\Http\RequestMethod;
use Columba\Http\ResponseCode;
use Columba\Router\Middleware\AbstractMiddleware;
use Columba\Router\Renderer\AbstractRenderer;
use Columba\Router\Response\AbstractResponse;
use Columba\Router\Response\ResponseWrapper;
use Columba\Router\Route\AbstractRoute;
use Columba\Router\Route\CallbackRoute;
use Columba\Router\Route\LazyRouterRoute;
use Columba\Router\Route\RedirectRoute;
use Columba\Router\Route\RouterRoute;
use Columba\Util\ServerTiming;
use Exception;
use ReflectionFunctionAbstract;
use ReflectionMethod;

/**
 * Class Router
 *
 * @package Columba\Router
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.0
 */
class Router
{

	/**
	 * @var array
	 */
	private $globals = [];

	/**
	 * @var AbstractMiddleware[]
	 */
	private $middlewares = [];

	/**
	 * @var string[]
	 */
	private $prefixes = [];

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
	private $routes = [];

	/**
	 * @var AbstractRoute|null
	 */
	private $currentRoute = null;

	/**
	 * Router constructor.
	 *
	 * @param AbstractResponse|null $response
	 * @param AbstractRenderer|null $renderer
	 * @param bool                  $isSubRouter
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(?AbstractResponse $response = null, ?AbstractRenderer $renderer = null, bool $isSubRouter = false)
	{
		if (!$isSubRouter)
			ServerTiming::start(Router::class, 'Router: Resolve Time', 'cpu');

		$this->renderer = $renderer;
		$this->response = $response;
	}

	/**
	 * Adds a {@see AbstractRoute}.
	 *
	 * @param AbstractRoute $route
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function add(AbstractRoute $route): void
	{
		if (strpos($route->getPath(), '/*') !== false)
			array_push($this->routes, $route);
		else
			array_unshift($this->routes, $route);
	}

	/**
	 * Tries to guess the {@see AbstractRoute} instance and adds it.
	 *
	 * @param string[] $requestMethods
	 * @param string   $path
	 * @param mixed    ...$arguments
	 *
	 * @return AbstractRoute
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function addFromArguments(array $requestMethods, string $path, ...$arguments): AbstractRoute
	{
		$prefix = implode('/', $this->prefixes);
		$route = null;

		if (mb_substr($path, 0, 1) !== '/')
			$path = '/' . ($prefix !== '' ? $prefix . '/' : '') . $path;

		if (count($arguments) > 0 && is_callable($arguments[0]))
			$route = new CallbackRoute($this, $requestMethods, $path, Closure::bind(Closure::fromCallable(array_shift($arguments)), $this), ...$arguments);

		else if (count($arguments) > 0 && $arguments[0] instanceof Router)
			$route = new RouterRoute($this, $requestMethods, $path, ...$arguments);

		else if (count($arguments) > 0 && is_string($arguments[0]) && is_subclass_of($arguments[0], Router::class))
			$route = new LazyRouterRoute($this, $requestMethods, $path, ...$arguments);

		else if (count($arguments) > 0 && $arguments[0] instanceof IGetRouter)
			$route = new RouterRoute($this, $requestMethods, $path, $arguments[0]->getRouter());

		else if (count($arguments) > 0 && $arguments[0] instanceof Closure)
			$route = new CallbackRoute($this, $requestMethods, $path, ...$arguments);

		if ($route === null && isset($arguments[0]) && is_array($arguments[0]) && is_string($arguments[0][1]))
			throw new RouterException(sprintf("Could not find implementation '%s' for route '%s' in '%s'!", $arguments[0][1], $path, get_called_class()), RouterException::ERR_NO_ROUTE_IMPLEMENTATION);
		else if ($route === null)
			throw new RouterException(sprintf("Could not determine route implementation for '%s' in '%s'!", $path, get_called_class()), RouterException::ERR_NO_ROUTE_IMPLEMENTATION);

		$this->add($route);

		return $route;
	}

	/**
	 * Defines a global parameter.
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function define(string $name, $value): void
	{
		$this->globals[$name] = $value;
	}

	/**
	 * Gets all global parameters.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 * @internal
	 */
	public function getGlobals(): array
	{
		return $this->globals;
	}

	/**
	 * Gets all enabled middlewares for this {@see Router}.
	 *
	 * @return AbstractMiddleware[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 * @internal
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
	 * @param string       $path
	 * @param string       $requestMethod
	 * @param Context|null $context
	 *
	 * @return AbstractRoute|null
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function find(string $path, string $requestMethod, ?Context $context = null): ?AbstractRoute
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
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function execute(string $path, string $requestMethod): void
	{
		if (($route = $this->find($path, $requestMethod)) !== null)
			$route->execute();
		else
			$this->onException(new RouterException(sprintf('Could not find route: %s', $path), RouterException::ERR_NOT_FOUND));
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
	public function render(string $template, array $context = []): string
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
	public function respond(string $implementation, $value, ...$options): ResponseWrapper
	{
		if (!is_subclass_of($implementation, AbstractResponse::class))
			throw new RouterException('Invalid response implementation! Needs to extend form AbstractResponse.', 0);

		return new ResponseWrapper(new $implementation(...$options), $value);
	}

	/**
	 * Adds a Router group.
	 *
	 * @param string   $path
	 * @param callable $predicate
	 *
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function group(string $path, callable $predicate): void
	{
		$this->prefixes[] = trim($path, '/');
		$predicate($this);
		array_splice($this->prefixes, count($this->prefixes) - 1, 1);
	}

	/**
	 * Adds an ALL {@see AbstractRoute}.
	 *
	 * @param string         $path
	 * @param callable&mixed $arguments
	 *
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function all(string $path, ...$arguments): void
	{
		$this->addFromArguments([], $path, ...$arguments);
	}

	/**
	 * Adds a route that matches everything in {@see $requestMethods}.
	 *
	 * @param array          $requestMethods
	 * @param string         $path
	 * @param callable&mixed $arguments
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function match(array $requestMethods, string $path, ...$arguments): void
	{
		$this->addFromArguments($requestMethods, $path, ...$arguments);
	}

	/**
	 * Adds a DELETE {@see AbstractRoute}.
	 *
	 * @param string         $path
	 * @param callable&mixed $arguments
	 *
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function delete(string $path, ...$arguments): void
	{
		$this->addFromArguments([RequestMethod::DELETE], $path, ...$arguments);
	}

	/**
	 * Adds a GET {@see AbstractRoute}.
	 *
	 * @param string         $path
	 * @param callable&mixed $arguments
	 *
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function get(string $path, ...$arguments): void
	{
		$this->addFromArguments([RequestMethod::GET], $path, ...$arguments);
	}

	/**
	 * Adds a HEAD {@see AbstractRoute}.
	 *
	 * @param string         $path
	 * @param callable&mixed $arguments
	 *
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function head(string $path, ...$arguments): void
	{
		$this->addFromArguments([RequestMethod::HEAD], $path, ...$arguments);
	}

	/**
	 * Adds an OPTIONS {@see AbstractRoute}.
	 *
	 * @param string         $path
	 * @param callable&mixed $arguments
	 *
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function options(string $path, ...$arguments): void
	{
		$this->addFromArguments([RequestMethod::OPTIONS], $path, ...$arguments);
	}

	/**
	 * Adds a PATCH {@see AbstractRoute}.
	 *
	 * @param string         $path
	 * @param callable&mixed $arguments
	 *
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function patch(string $path, ...$arguments): void
	{
		$this->addFromArguments([RequestMethod::PATCH], $path, ...$arguments);
	}

	/**
	 * Adds a POST {@see AbstractRoute}.
	 *
	 * @param string         $path
	 * @param callable&mixed $arguments
	 *
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function post(string $path, ...$arguments): void
	{
		$this->addFromArguments([RequestMethod::POST], $path, ...$arguments);
	}

	/**
	 * Adds a PUT {@see AbstractRoute}.
	 *
	 * @param string         $path
	 * @param callable&mixed $arguments
	 *
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function put(string $path, ...$arguments): void
	{
		$this->addFromArguments([RequestMethod::PUT], $path, ...$arguments);
	}

	/**
	 * Adds a redirect {@see AbstractRoute}.
	 *
	 * @param string   $path
	 * @param string   $destination
	 * @param string[] $requestMethods
	 * @param int      $responseCode
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.1
	 */
	public final function redirect(string $path, string $destination, array $requestMethods = [], int $responseCode = ResponseCode::SEE_OTHER): void
	{
		$this->add(new RedirectRoute($this, $requestMethods, $path, $destination, $responseCode));
	}

	/**
	 * @return AbstractRoute|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getCurrentRoute(): ?AbstractRoute
	{
		return $this->currentRoute;
	}

	/**
	 * @param AbstractRoute|null $currentRoute
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function setCurrentRoute(?AbstractRoute $currentRoute)
	{
		$this->currentRoute = $currentRoute;
	}

	/**
	 * Invoked when a {@see AbstractRoute} is executed.
	 *
	 * @param AbstractRoute $route
	 * @param Context       $context
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function onExecute(AbstractRoute $route, Context $context): void
	{
	}

	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 *
	 * Invoked when an {@see Exception} is thrown.
	 *
	 * @param Exception    $err
	 * @param Context|null $context
	 *
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function onException(Exception $err, ?Context $context = null): void
	{
		if ($err instanceof RouterException)
			/** @noinspection PhpUnhandledExceptionInspection */
			throw $err;

		$callbackName = function (ReflectionFunctionAbstract $callback): string
		{
			if ($callback instanceof ReflectionMethod)
				return $callback->getDeclaringClass()->getName() . '::' . $callback->getName();

			return $callback->getName();
		};

		if ($context !== null && $context->getCallback() !== null)
			throw new RouterException(sprintf("Exception thrown while executing %s for route '%s'.", $callbackName($context->getCallback()), $context->getFullPath(false)), RouterException::ERR_ROUTE_THREW_EXCEPTION, $err);

		if ($context !== null)
			throw new RouterException(sprintf("Exception thrown while executing '%s'.", $context->getFullPath(false)), RouterException::ERR_ROUTE_THREW_EXCEPTION, $err);

		throw new RouterException('Route handler threw an exception!', RouterException::ERR_ROUTE_THREW_EXCEPTION, $err);
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
