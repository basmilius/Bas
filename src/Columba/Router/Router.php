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

use Closure;
use Columba\Facade\Debuggable;
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
use JetBrains\PhpStorm\ArrayShape;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use function array_filter;
use function array_merge;
use function array_push;
use function array_shift;
use function array_splice;
use function array_unshift;
use function class_exists;
use function count;
use function get_called_class;
use function implode;
use function is_array;
use function is_callable;
use function is_string;
use function is_subclass_of;
use function sprintf;
use function trim;

/**
 * Class Router
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router
 * @since 1.3.0
 */
class Router implements Debuggable
{

	private array $globals = [];
	private ?AbstractRoute $currentRoute = null;

	/** @var AbstractMiddleware[] */
	private array $middlewares = [];

	/** @var string[] */
	private array $prefixes = [];

	/** @var AbstractRoute[] */
	private array $routes = [];

	private ?int $groupId = null;
	private array $groupMiddlewares = [];
	/** @var AbstractRoute[][] */
	private array $groupRoutes = [];

	/**
	 * Router constructor.
	 *
	 * @param AbstractResponse|null $response
	 * @param AbstractRenderer|null $renderer
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(private ?AbstractResponse $response = null, private ?AbstractRenderer $renderer = null)
	{
	}

	/**
	 * Adds a {@see AbstractRoute}.
	 *
	 * @param AbstractRoute $route
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function add(AbstractRoute $route): void
	{
		if (str_contains($route->getPath(), '/*'))
			array_push($this->routes, $route);
		else
			array_unshift($this->routes, $route);

		if ($this->groupId !== null)
			$this->groupRoutes[$this->groupId][] = $route;
	}

	/**
	 * Tries to guess the {@see AbstractRoute} instance and adds it.
	 *
	 * @param string[] $requestMethods
	 * @param string $path
	 * @param mixed ...$arguments
	 *
	 * @return AbstractRoute
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function addFromArguments(array $requestMethods, string $path, ...$arguments): AbstractRoute
	{
		$path = array_filter(array_merge($this->prefixes, [trim($path, '/')]));
		$path = '/' . implode('/', $path);
		$route = null;

		if (count($arguments) > 0 && is_callable($arguments[0]))
			$route = new CallbackRoute($this, $requestMethods, $path, Closure::bind(Closure::fromCallable(array_shift($arguments)), $this));

		else if (count($arguments) > 0 && $arguments[0] instanceof Router)
			$route = new RouterRoute($this, $requestMethods, $path, ...$arguments);

		else if (count($arguments) > 0 && is_string($arguments[0]) && is_subclass_of($arguments[0], Router::class))
			$route = new LazyRouterRoute($this, $requestMethods, $path, ...$arguments);

		else if (count($arguments) > 0 && $arguments[0] instanceof IGetRouter)
			$route = new RouterRoute($this, $requestMethods, $path, $arguments[0]->getRouter());

		else if (count($arguments) > 0 && $arguments[0] instanceof Closure)
			$route = new CallbackRoute($this, $requestMethods, $path, ...$arguments);

		if ($route === null && isset($arguments[0]) && is_array($arguments[0]) && is_string($arguments[0][1]))
			throw new RouterException(sprintf("Could not find implementation '%s' for route '%s' in '%s'.", $arguments[0][1], $path, get_called_class()), RouterException::ERR_NO_ROUTE_IMPLEMENTATION);
		else if ($route === null)
			throw new RouterException(sprintf("Could not determine route implementation for '%s' in '%s'.", $path, get_called_class()), RouterException::ERR_NO_ROUTE_IMPLEMENTATION);

		$this->add($route);

		return $route;
	}

	/**
	 * Defines a global parameter.
	 *
	 * @param string $name
	 * @param mixed $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function define(string $name, mixed $value): void
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
	public function getMiddlewares(): array
	{
		return $this->middlewares;
	}

	/**
	 * Gets all the registered routes.
	 *
	 * @return AbstractRoute[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @internal
	 */
	public function getRoutes(): array
	{
		return $this->routes;
	}

	/**
	 * Adds a {@see AbstractRoute} to use.
	 *
	 * @param string $middleware
	 * @param mixed ...$arguments
	 *
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function use(string $middleware, mixed ...$arguments): void
	{
		if (!class_exists($middleware))
			throw new RouterException(sprintf('Middleware %s not found.', $middleware), RouterException::ERR_MIDDLEWARE_NOT_FOUND);

		if (!is_subclass_of($middleware, AbstractMiddleware::class))
			throw new RouterException(sprintf('Class %s needs to extend from %s to be a valid middleware.', $middleware, AbstractMiddleware::class), RouterException::ERR_MIDDLEWARE_INVALID);

		if ($this->groupId !== null)
			$this->groupMiddlewares[$this->groupId][] = [$middleware, $arguments];
		else
			$this->middlewares[] = new $middleware($this, ...$arguments);
	}

	/**
	 * Searches for a matching {@see AbstractRoute}.
	 *
	 * @param string $path
	 * @param string $requestMethod
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
		ServerTiming::start(Router::class, 'Router: Resolve Time');

		if (($route = $this->find($path, $requestMethod)) !== null)
			$route->execute();
		else
			$this->onException(new RouterException(sprintf('Could not find route: %s', $path), RouterException::ERR_NOT_FOUND));
	}

	/**
	 * Renders a {@see $template} with the given {@see $context} using an {@see AbstractRenderer}.
	 *
	 * @param string $template
	 * @param array $context
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
	 * @param mixed $value
	 * @param mixed ...$options
	 *
	 * @return ResponseWrapper
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function respond(string $implementation, mixed $value, mixed ...$options): ResponseWrapper
	{
		if (!is_subclass_of($implementation, AbstractResponse::class))
			throw new RouterException('Invalid response implementation! Needs to extend form AbstractResponse.', 0);

		return new ResponseWrapper(new $implementation(...$options), $value);
	}

	/**
	 * Adds a Router group.
	 *
	 * @param string $path
	 * @param callable $predicate
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function group(string $path, callable $predicate): void
	{
		$this->groupId = count($this->prefixes);
		$this->groupMiddlewares[$this->groupId] = [];

		$this->prefixes[] = trim($path, '/');

		$predicate($this);

		foreach ($this->groupMiddlewares[$this->groupId] as [$middleware, $arguments])
			foreach ($this->groupRoutes[$this->groupId] as $route)
				$route->middleware($middleware, ...$arguments);

		unset($this->groupMiddlewares[$this->groupId]);
		unset($this->groupRoutes[$this->groupId]);

		array_splice($this->prefixes, $this->groupId, 1);
		$this->groupId = ($count = count($this->prefixes)) === 0 ? null : $count;
	}

	/**
	 * Adds an ALL {@see AbstractRoute}.
	 *
	 * @param string $path
	 * @param callable&mixed $arguments
	 *
	 * @return AbstractRoute
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function all(string $path, mixed ...$arguments): AbstractRoute
	{
		return $this->addFromArguments([], $path, ...$arguments);
	}

	/**
	 * Adds a route that matches everything in {@see $requestMethods}.
	 *
	 * @param array $requestMethods
	 * @param string $path
	 * @param callable&mixed $arguments
	 *
	 * @return AbstractRoute
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function match(array $requestMethods, string $path, mixed ...$arguments): AbstractRoute
	{
		return $this->addFromArguments($requestMethods, $path, ...$arguments);
	}

	/**
	 * Adds a DELETE {@see AbstractRoute}.
	 *
	 * @param string $path
	 * @param callable&mixed $arguments
	 *
	 * @return AbstractRoute
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function delete(string $path, mixed ...$arguments): AbstractRoute
	{
		return $this->addFromArguments([RequestMethod::DELETE], $path, ...$arguments);
	}

	/**
	 * Adds a GET {@see AbstractRoute}.
	 *
	 * @param string $path
	 * @param callable&mixed $arguments
	 *
	 * @return AbstractRoute
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function get(string $path, mixed ...$arguments): AbstractRoute
	{
		return $this->addFromArguments([RequestMethod::GET], $path, ...$arguments);
	}

	/**
	 * Adds a HEAD {@see AbstractRoute}.
	 *
	 * @param string $path
	 * @param callable&mixed $arguments
	 *
	 * @return AbstractRoute
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function head(string $path, mixed ...$arguments): AbstractRoute
	{
		return $this->addFromArguments([RequestMethod::HEAD], $path, ...$arguments);
	}

	/**
	 * Adds an OPTIONS {@see AbstractRoute}.
	 *
	 * @param string $path
	 * @param callable&mixed $arguments
	 *
	 * @return AbstractRoute
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function options(string $path, mixed ...$arguments): AbstractRoute
	{
		return $this->addFromArguments([RequestMethod::OPTIONS], $path, ...$arguments);
	}

	/**
	 * Adds a PATCH {@see AbstractRoute}.
	 *
	 * @param string $path
	 * @param callable&mixed $arguments
	 *
	 * @return AbstractRoute
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function patch(string $path, mixed ...$arguments): AbstractRoute
	{
		return $this->addFromArguments([RequestMethod::PATCH], $path, ...$arguments);
	}

	/**
	 * Adds a POST {@see AbstractRoute}.
	 *
	 * @param string $path
	 * @param callable&mixed $arguments
	 *
	 * @return AbstractRoute
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function post(string $path, mixed ...$arguments): AbstractRoute
	{
		return $this->addFromArguments([RequestMethod::POST], $path, ...$arguments);
	}

	/**
	 * Adds a PUT {@see AbstractRoute}.
	 *
	 * @param string $path
	 * @param callable&mixed $arguments
	 *
	 * @return AbstractRoute
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function put(string $path, mixed ...$arguments): AbstractRoute
	{
		return $this->addFromArguments([RequestMethod::PUT], $path, ...$arguments);
	}

	/**
	 * Adds a redirect {@see AbstractRoute}.
	 *
	 * @param string $path
	 * @param string $destination
	 * @param string[] $requestMethods
	 * @param int $responseCode
	 *
	 * @return AbstractRoute
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.1
	 */
	public function redirect(string $path, string $destination, array $requestMethods = [], int $responseCode = ResponseCode::SEE_OTHER): AbstractRoute
	{
		$route = new RedirectRoute($this, $requestMethods, $path, $destination, $responseCode);

		$this->add($route);

		return $route;
	}

	/**
	 * Gets the current {@see AbstractRoute}.
	 *
	 * @return AbstractRoute|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function getCurrentRoute(): ?AbstractRoute
	{
		return $this->currentRoute;
	}

	/**
	 * @param AbstractRoute|null $currentRoute
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function setCurrentRoute(?AbstractRoute $currentRoute): void
	{
		$this->currentRoute = $currentRoute;
	}

	/**
	 * Invoked when a {@see AbstractRoute} is executed.
	 *
	 * @param AbstractRoute $route
	 * @param Context $context
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
	 * @param Exception $err
	 * @param Context|null $context
	 *
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function onException(Exception $err, ?Context $context = null): void
	{
		if ($this instanceof SubRouter && ($parent = $this->getParent()) !== null)
		{
			$parent->onException($err, $context);

			return;
		}

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
	 * @return AbstractResponse|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function getResponse(): ?AbstractResponse
	{
		return $this->response;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	#[ArrayShape([
		'globals' => 'array',
		'renderer' => '\Columba\Router\Renderer\AbstractRenderer|null',
		'response' => '\Columba\Router\Response\AbstractResponse|null',
		'middlewares' => '\Columba\Router\Middleware\AbstractMiddleware[]',
		'routes' => '\Columba\Router\Route\AbstractRoute[]'
	])]
	public function __debugInfo(): array
	{
		return [
			'globals' => $this->globals,
			'renderer' => $this->renderer,
			'response' => $this->response,
			'middlewares' => $this->middlewares,
			'routes' => $this->routes
		];
	}

}
