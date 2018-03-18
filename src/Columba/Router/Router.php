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

use Closure;
use Columba\Router\Exception\AccessDeniedException;
use Columba\Router\Exception\RouteExecutionException;
use Columba\Router\Renderer\AbstractRenderer;
use Columba\Router\Response\AbstractResponse;
use Columba\Router\Response\HtmlResponse;
use Columba\Router\Response\JsonResponse;
use Exception;
use JsonSerializable;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

define('HTTP_DELETE', 'DELETE');
define('HTTP_GET', 'GET');
define('HTTP_OPTIONS', 'OPTIONS');
define('HTTP_PATCH', 'PATCH');
define('HTTP_POST', 'POST');
define('HTTP_PUT', 'PUT');

/**
 * Class Router
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router
 * @since 1.0.0
 */
class Router
{

	private const ALLOWED_METHODS = [HTTP_DELETE, HTTP_GET, HTTP_OPTIONS, HTTP_PATCH, HTTP_POST, HTTP_PUT];

	/**
	 * @var Router
	 */
	private $parent;

	/**
	 * @var AbstractRenderer|null
	 */
	private $renderer;

	/**
	 * @var AbstractResponse|null
	 */
	private $response;

	/**
	 * @var array
	 */
	private $routes;

	/**
	 * Router constructor.
	 *
	 * @param AbstractResponse|null $response
	 * @param AbstractRenderer|null $renderer
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct (?AbstractResponse $response = null, ?AbstractRenderer $renderer = null)
	{
		$this->parent = null;
		$this->renderer = $renderer;
		$this->response = $response;
		$this->routes = [];
	}

	/**
	 * Returns TRUE if the router is available for the current context.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	protected function canAccess (): bool
	{
		return true;
	}

	/**
	 * Changes a param before it's used by the {@see Router}.
	 *
	 * @param string      $paramName
	 * @param mixed       $paramValue
	 * @param string|null $paramType
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	protected function changeParam (string $paramName, $paramValue, ?string $paramType = null)
	{
		if ($paramValue === null)
			return null;

		if (empty($paramName))
			return null;

		if ($paramType === 's')
			return strval($paramValue);

		if ($paramType === 'i')
			return intval($paramValue);

		if ($paramType === 'f')
			return floatval($paramValue);

		if ($paramType === 'b')
			return strval($paramValue) === '1' || strval($paramValue) === 'true';

		return $paramValue;
	}

	/** @noinspection PhpDocRedundantThrowsInspection */
	/**
	 * Handles the request based on {@see $requestPath}.
	 *
	 * @param string $requestPath
	 * @param array  $params
	 * @param bool   $isSubRoute
	 *
	 * @throws AccessDeniedException
	 * @throws RouteExecutionException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	protected function handle (string $requestPath, array $params = [], bool $isSubRoute = false): void
	{
		$currentPath = null;
		$didHandleRequest = false;

		foreach ($this->routes as [$path, $handler, $requestMethod, $overrideResponse])
		{
			if ($requestMethod !== null && $_SERVER['REQUEST_METHOD'] !== $requestMethod)
				continue;

			$paramDefinitions = [];
			$pattern = '';
			$parts = array_filter(explode('/', $path));

			foreach ($parts as $part)
			{
				preg_match('#\[(i|s|f|b)\:([a-zA-Z0-9-_\.]+)\]#', $part, $matches);

				if (count($matches) < 3 || count($matches) === 0)
				{
					$pattern .= '\/' . $part;
				}
				else
				{
					$paramType = $matches[1];
					$paramName = $matches[2];

					$paramDefinitions[] = [$paramName, $paramType];

					if ($paramType === 's')
						$pattern .= '\/(?<' . $paramName . '>[a-zA-Z0-9-_\.]+)';
					else if ($paramType === 'i')
						$pattern .= '\/(?<' . $paramName . '>[0-9]+)';
					else if ($paramType === 'f')
						$pattern .= '\/(?<' . $paramName . '>[0-9\.]+)';
					else if ($paramType === 'b')
						$pattern .= '\/(?<' . $paramName . '>(0|1|false|true)+)';
				}
			}

			$pattern = '#^' . $pattern . '(?![a-zA-Z0-9-_])#';
			$isMatch = preg_match($pattern, $requestPath, $matches);

			foreach ($paramDefinitions as $paramDefinition)
			{
				$paramName = $paramDefinition[0];
				$paramType = $paramDefinition[1];

				if (!isset($matches[$paramName]))
					continue;

				$params[$paramName] = $this->changeParam($paramName, $matches[$paramName], $paramType);
			}

			if (!$isMatch)
				continue;

			$convertedPath = $path;

			foreach ($params as $param => $value)
				$convertedPath = preg_replace('#(\[(s|i|f|b)\:(' . $param . ')\])#', $value, $convertedPath);

			$newPath = substr($requestPath, strlen($convertedPath));

			if (!$newPath)
				$newPath = '/';

			if (substr($newPath, 0, 1) !== '/')
				$newPath = '/' . $newPath;

			try
			{
				if ($handler instanceof LateInitRouter)
					$handler = $handler->createRouter();

				if ($handler instanceof self)
				{
					if (!$handler->canAccess())
						continue;

					try
					{
						$handler->handle($newPath, $params, true);
						$didHandleRequest = true;
					}
					catch (RouteExecutionException $err)
					{
						if ($err->getCode() === RouteExecutionException::ERR_SUBROUTE_NOT_FOUND)
							$didHandleRequest = false;
						else
							throw $err;
					}
				}
				else if (preg_match(substr($pattern, 0, -1) . '$#', $requestPath) || $requestPath === '/' && $path === '/')
				{
					$response = $overrideResponse ?? $this->response();

					if (is_string($handler) && !is_callable($handler))
					{
						$this->response()->redirect($handler);

						$didHandleRequest = true;
					}
					else if (is_callable($handler))
					{
						$reflection = !($handler instanceof Closure) ? new ReflectionMethod($handler[0], $handler[1]) : new ReflectionFunction($handler);
						$this->parseArguments($reflection->getParameters(), $params, $arguments);

						/** @var IOnParameters $instance */
						if ($reflection instanceof ReflectionMethod && ($instance = $handler[0]) instanceof IOnParameters)
							$instance->onParameters($arguments, $params);

						$data = call_user_func_array($handler, $arguments);
						$response->print($data);

						$didHandleRequest = true;
					}
					else
					{
						throw new RouteExecutionException('Route callback is inaccessible', RouteExecutionException::ERR_INACCESSIBLE);
					}
				}
			}
			catch (AccessDeniedException $err)
			{
				$this->onAccessDenied($err);
				$didHandleRequest = true;
			}
			catch (JsonSerializable $err)
			{
				$this->response(new JsonResponse(false))->print($err);
				$didHandleRequest = true;
			}
			catch (Exception $exception)
			{
				$this->onException($exception);
			}

			if ($didHandleRequest)
				break;
		}

		if (!$didHandleRequest)
		{
			if ($isSubRoute)
				throw new RouteExecutionException('Subroute not found', RouteExecutionException::ERR_SUBROUTE_NOT_FOUND);
			else
				$this->onNotFound();
		}
	}

	/**
	 * Parses the parameters to their defined types.
	 *
	 * @param ReflectionParameter[] $reflectionArguments
	 * @param array                 $params
	 * @param array|null            $arguments
	 *
	 * @throws RouteExecutionException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function parseArguments (array $reflectionArguments, array $params, ?array &$arguments): void
	{
		$arguments = [];

		if (count($reflectionArguments) < count($params))
			return;

		$notPopulated = $reflectionArguments;
		/** @var ReflectionParameter[] $notPopulated */
		$notPopulated = array_splice($notPopulated, count($params));

		foreach ($notPopulated as $methodParam)
			if (isset($_REQUEST[$methodParam->getName()]))
				$params[$methodParam->getName()] = $_REQUEST[$methodParam->getName()];

		foreach ($reflectionArguments as $parameter)
		{
			if (isset($params[$parameter->getName()]))
			{
				$arguments[$parameter->getName()] = $params[$parameter->getName()];
			}
			else if ($parameter->isDefaultValueAvailable())
			{
				$arguments[$parameter->getName()] = $parameter->getDefaultValue();
			}
			else
			{
				throw new RouteExecutionException('Some route parameters were missing', RouteExecutionException::ERR_MISSING_PARAMETERS);
			}
		}
	}

	/**
	 * Adds a DELETE route handler.
	 *
	 * @param string                                    $path
	 * @param Router|LateInitRouter|IGetRouter|callable $route
	 * @param AbstractResponse|null                     $overrideResponse
	 *
	 * @see Router::use()
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function delete (string $path, $route, ?AbstractResponse $overrideResponse = null): void
	{
		$this->use($path, $route, 'DELETE', $overrideResponse);
	}

	/**
	 * Adds a GET route handler.
	 *
	 * @param string                                    $path
	 * @param Router|LateInitRouter|IGetRouter|callable $route
	 * @param AbstractResponse|null                     $overrideResponse
	 *
	 * @see Router::use()
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function get (string $path, $route, ?AbstractResponse $overrideResponse = null): void
	{
		$this->use($path, $route, 'GET', $overrideResponse);
	}

	/**
	 * Adds a OPTIONS route handler.
	 *
	 * @param string                                    $path
	 * @param Router|LateInitRouter|IGetRouter|callable $route
	 * @param AbstractResponse|null                     $overrideResponse
	 *
	 * @see Router::use()
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function options (string $path, $route, ?AbstractResponse $overrideResponse = null): void
	{
		$this->use($path, $route, 'OPTIONS', $overrideResponse);
	}

	/**
	 * Adds a PATCH route handler.
	 *
	 * @param string                                    $path
	 * @param Router|LateInitRouter|IGetRouter|callable $route
	 * @param AbstractResponse|null                     $overrideResponse
	 *
	 * @see Router::use()
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function patch (string $path, $route, ?AbstractResponse $overrideResponse = null): void
	{
		$this->use($path, $route, 'PATCH', $overrideResponse);
	}

	/**
	 * Adds a POST route handler.
	 *
	 * @param string                                    $path
	 * @param Router|LateInitRouter|IGetRouter|callable $route
	 * @param AbstractResponse|null                     $overrideResponse
	 *
	 * @see Router::use()
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function post (string $path, $route, ?AbstractResponse $overrideResponse = null): void
	{
		$this->use($path, $route, 'POST', $overrideResponse);
	}

	/**
	 * Adds a PUT route handler.
	 *
	 * @param string                                    $path
	 * @param Router|LateInitRouter|IGetRouter|callable $route
	 * @param AbstractResponse|null                     $overrideResponse
	 *
	 * @see Router::use()
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function put (string $path, $route, ?AbstractResponse $overrideResponse = null): void
	{
		$this->use($path, $route, 'PUT', $overrideResponse);
	}

	/**
	 * Adds a redirect path.
	 *
	 * @param string      $path
	 * @param string      $newPath
	 * @param string|null $requestMethod
	 *
	 * @see Router::use()
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function redirect (string $path, string $newPath, ?string $requestMethod = null): void
	{
		$this->use($path, $newPath, $requestMethod);
	}

	/**
	 * Adds a route with handlers for multiple request methods.
	 *
	 * @param string $path
	 * @param array  $handlers
	 *
	 * @see Router::use()
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function for (string $path, array $handlers): void
	{
		foreach ($handlers as $requestMethod => $handler)
		{
			if (!in_array($requestMethod, self::ALLOWED_METHODS))
				continue;

			$overrideResponse = null;

			if (is_array($handler) && !is_callable($handler))
			{
				$overrideResponse = $handler[1];
				$handler = $handler[0];
			}

			$this->use($path, $handler, $requestMethod, $overrideResponse);
		}
	}

	/**
	 * Adds a route handler on a {@see $path}.
	 *
	 * @param string                                           $path
	 * @param Router|LateInitRouter|IGetRouter|callable|string $route
	 * @param string|null                                      $requestMethod
	 * @param AbstractResponse|null                            $overrideResponse
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function use (string $path, $route, ?string $requestMethod = null, ?AbstractResponse $overrideResponse = null): void
	{
		if ($route instanceof IGetRouter)
			$route = $route->getRouter();

		if ($route instanceof self || $route instanceof LateInitRouter)
			$route->setParent($this);

		array_unshift($this->routes, [$path, $route, $requestMethod, $overrideResponse]);
	}

	/**
	 * Renders a {@see $template} with the given {@see $context}.
	 *
	 * @param string $template
	 * @param array  $context
	 *
	 * @return string
	 * @throws RouteExecutionException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 * @see AbstractRenderer
	 */
	public final function render (string $template, array $context = []): string
	{
		if ($this->getRenderer() === null)
			throw new RouteExecutionException('Cannot render template without an Columba\\AbstractRenderer instance!');

		return $this->getRenderer()->render($template, $context);
	}

	/**
	 * Gets the response handler.
	 *
	 * @param AbstractResponse|null $response
	 *
	 * @return AbstractResponse
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 * @see AbstractResponse
	 */
	public final function response (?AbstractResponse $response = null): AbstractResponse
	{
		if ($response !== null)
			$this->response = $response;

		if ($this->getResponse() !== null)
			return $this->getResponse();

		return new HtmlResponse();
	}

	/**
	 * Gets a route.
	 *
	 * @param string $route
	 *
	 * @return Router|callable|string|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getRoute (string $route)
	{
		foreach ($this->routes as [$path, $handler])
			if ($route === $path)
				return $handler;

		return null;
	}

	/**
	 * Gets the routes in this {@see Router}.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getRoutes (): array
	{
		return $this->routes;
	}

	/**
	 * Gets the parent {@see Router}.
	 *
	 * @return Router|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getParent (): ?Router
	{
		return $this->parent;
	}

	/**
	 * Sets the parent {@see Router}.
	 *
	 * @param Router $parent
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function setParent (Router $parent): void
	{
		$this->parent = $parent;
	}

	/**
	 * Invoked when route access is denied.
	 *
	 * @param AccessDeniedException $err
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	protected function onAccessDenied (AccessDeniedException $err): void
	{
		$this->response->print('Access to this route is denied. ' . $err->getMessage());
	}

	/**
	 * Invoked when an exception is thrown.
	 *
	 * @param Exception $exception
	 *
	 * @throws RouteExecutionException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected function onException (Exception $exception): void
	{
		if ($exception instanceof RouteExecutionException)
			throw $exception;
		else
			throw new RouteExecutionException('Route execution failed with an exception', RouteExecutionException::ERR_ROUTE_THREW_EXCEPTION, $exception);
	}

	/**
	 * Invoked when a route is not found.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	protected function onNotFound (): void
	{
		$this->response->print('Route not found!');
	}

	/**
	 * Gets the recursive renderer instance.
	 *
	 * @return AbstractRenderer|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 3.0.0
	 */
	protected final function getRenderer (): ?AbstractRenderer
	{
		if ($this->renderer !== null)
			return $this->renderer;

		if ($this->parent !== null)
			return $this->parent->getRenderer();

		return null;
	}

	/**
	 * Gets the recursive response instance.
	 *
	 * @return AbstractRenderer|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 3.0.0
	 */
	protected final function getResponse (): ?AbstractResponse
	{
		if ($this->response !== null)
			return $this->response;

		if ($this->parent !== null)
			return $this->parent->getResponse();

		return null;
	}

}
