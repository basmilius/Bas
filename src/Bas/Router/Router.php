<?php
declare(strict_types=1);

namespace Bas\Router;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Class Router
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Router
 * @since 1.0.0
 */
class Router
{

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

	/**
	 * Handles the request based on {@see $requestPath}.
	 *
	 * @param string $requestPath
	 * @param array  $params
	 * @param bool   $isSubRoute
	 *
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
					{
						$pattern .= '\/(?<' . $paramName . '>[a-zA-Z0-9-_\.]+)';
					}
					else if ($paramType === 'i')
					{
						$pattern .= '\/(?<' . $paramName . '>[0-9]+)';
					}
					else if ($paramType === 'f')
					{
						$pattern .= '\/(?<' . $paramName . '>[0-9\.]+)';
					}
					else if ($paramType === 'b')
					{
						$pattern .= '\/(?<' . $paramName . '>(0|1|false|true)+)';
					}
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

			try
			{
				if ($handler instanceof self)
				{
					if (!$handler->canAccess())
						continue;

					$newPath = substr($requestPath, strlen($convertedPath));

					if (!$newPath)
						$newPath = '/';

					if (substr($newPath, 0, 1) !== '/')
						$newPath = '/' . $newPath;

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

					if (is_string($handler) && class_exists($handler) && ($reflection = new ReflectionClass($handler))->isSubclassOf(AbstractRouteController::class))
					{
						$this->parseArguments($reflection->getConstructor()->getParameters(), $params, $arguments);

						/** @var AbstractRouteController $instance */
						$instance = $reflection->newInstanceArgs($arguments);
						$instance->onBeforeHandle();

						$response->print($instance->handle());

						$instance->onAfterHandle();

						$didHandleRequest = true;
					}
					else if (is_string($handler) && !is_callable($handler))
					{
						$this->response()->redirect($handler);

						$didHandleRequest = true;
					}
					else if (is_callable($handler))
					{
						$reflection = !($handler instanceof Closure) ? new ReflectionMethod($handler[0], $handler[1]) : new ReflectionFunction($handler);
						$this->parseArguments($reflection->getParameters(), $params, $arguments);

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
				/** @noinspection PhpRedundantCatchClauseInspection */
			catch (AccessDeniedException $err)
			{
				$this->onAccessDenied($err);
				$didHandleRequest = true;
			}
			catch (Exception $exception)
			{
				throw new RouteExecutionException('Route execution failed with an exception', RouteExecutionException::ERR_ROUTE_THREW_EXCEPTION, $exception);
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
	 * Adds a GET route handler.
	 *
	 * @param string                $path
	 * @param Router|callable       $route
	 * @param AbstractResponse|null $overrideResponse
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
	 * Adds a DELETE route handler.
	 *
	 * @param string                $path
	 * @param Router|callable       $route
	 * @param AbstractResponse|null $overrideResponse
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
	 * Adds a PATCH route handler.
	 *
	 * @param string                $path
	 * @param Router|callable       $route
	 * @param AbstractResponse|null $overrideResponse
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
	 * @param string                $path
	 * @param Router|callable       $route
	 * @param AbstractResponse|null $overrideResponse
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
	 * @param string                $path
	 * @param Router|callable       $route
	 * @param AbstractResponse|null $overrideResponse
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
	 * Adds a route handler on a {@see $path}.
	 *
	 * @param string                 $path
	 * @param Router|callable|string $route
	 * @param string|null            $requestMethod
	 * @param AbstractResponse|null  $overrideResponse
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function use (string $path, $route, ?string $requestMethod = null, ?AbstractResponse $overrideResponse = null): void
	{
		if ($route instanceof self)
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
		if ($this->renderer === null)
			throw new RouteExecutionException('Cannot render template without an Bas\\AbstractRenderer instance!');

		return $this->renderer->render($template, $context);
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

		if ($this->response !== null)
			return $this->response;

		if ($this->parent !== null)
			return $this->parent->response();

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
	public final function getParent (): ?self
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
	public final function setParent (self $parent): void
	{
		$this->parent = $parent;

		$this->renderer = $this->renderer ?? $parent->renderer;
		$this->response = $this->response ?? $parent->response;
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
	 * Invoked when a route is not found.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	protected function onNotFound (): void
	{
		$this->response->print('Route not found!');
	}

}
