<?php
declare(strict_types=1);

namespace Columba\Router\Route;

use Columba\Router\Response\ResponseWrapper;
use Columba\Router\RouteContext;
use Columba\Router\RouteParam;
use Columba\Router\Router;
use Columba\Router\RouterException;
use Columba\Util\A;
use Exception;

/**
 * Class AbstractRoute
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router\Route
 * @since 1.3.0
 */
abstract class AbstractRoute
{

	/**
	 * @var bool
	 */
	private $allowSubRoutes;

	/**
	 * @var RouteContext
	 */
	private $context;

	/**
	 * @var array
	 */
	private $options;

	/**
	 * @var string
	 */
	private $path;

	/**
	 * @var string|null
	 */
	private $requestMethod;

	/**
	 * @var Router
	 */
	private $parent;

	/**
	 * AbstractRoute constructor.
	 *
	 * @param Router $parent
	 * @param string $path
	 * @param array  $options
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(Router $parent, string $path, array $options = [])
	{
		$this->allowSubRoutes = false;
		$this->context = null;
		$this->options = $options;
		$this->path = $path;
		$this->requestMethod = null;
		$this->parent = $parent;
	}

	/**
	 * Executes the {@see AbstractRoute}.
	 *
	 * @param bool $respond
	 *
	 * @return mixed
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function execute(bool $respond)
	{
		$result = null;
		$throw = null;

		try
		{
			if ($this->getContext()->getRedirectPath() === null)
				$result = $this->executeImpl($respond);

			if ($this->getContext()->getRedirectPath() === null)
				return $result;

			http_response_code($this->getContext()->getRedirectCode());
			header('Location: ' . $this->resolve($this->getContext()->getRedirectPath()));
			return $result;
		}
		catch (Exception $err)
		{
			return $this->getParentRouter()->onException($err);
		}
	}

	/**
	 * Implementation for {@see AbstractRoute::execute()}.
	 *
	 * @param bool $respond
	 *
	 * @throws RouterException
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public abstract function executeImpl(bool $respond);

	/**
	 * Resolves a path relative to this {@see AbstractRoute}.
	 *
	 * @param string $path
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function resolve(string $path): string
	{
		if (substr($path, 0, 1) === '/' || substr($path, 0, 4) === 'http')
			return $path; // No need to resolve.

		$parts = explode('/', substr($this->context->getFullPath(), 0, -mb_strlen($this->path)) . '/' . $path);
		$result = [];

		foreach ($parts as $part)
		{
			if (mb_strlen($part) === 0 || $part === '.')
				continue;

			if ($part !== '..')
				$result[] = $part;
			else if (count($result) > 0)
				A::pop($result);
		}

		return '/' . implode('/', $result);
	}

	/**
	 * Responds to the output buffer.
	 *
	 * @param $value
	 *
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function respond($value): void
	{
		if ($value instanceof ResponseWrapper)
		{
			$response = $value->getResponse();
			$value = $value->getValue();
		}
		else
		{
			$response = $this->getParentRouter()->getResponse();
		}

		if ($response === null)
			throw new RouterException('Missing response implementation', 0);

		$response->print($value);
	}

	/**
	 * Gets validatable params.
	 *
	 * @return RouteParam[]
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public abstract function getValidatableParams(): array;

	/**
	 * Checks if this route is a match with {@see $path}.
	 *
	 * @param string $path
	 * @param string $requestMethod
	 *
	 * @return bool
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function isMatch(string $path, string $requestMethod): bool
	{
		if (strlen($path) > 1 && substr($path, -1) === '/')
			$path = substr($path, 0, -1);

		if ($path === '/index')
			$path = '/';

		$params = $this->getValidatableParams();
		$paramsValues = [];
		$pathRegex = $this->path;
		$pathValues = $this->path;

		foreach ($params as $param)
			$pathRegex = str_replace('/$' . $param->getName(), $param->getRegex(), $pathRegex);

		$pathRegex = '#^' . $pathRegex . (!$this->allowSubRoutes ? '$' : '') . '#';
		$isRouteValid = preg_match($pathRegex, $path, $matches);
		$isRouteValid = $isRouteValid && substr($path, 0, mb_strlen($matches[0])) === $matches[0];

		foreach ($params as $index => $param)
		{
			$paramsValues[$param->getName()] = isset($matches[$index + 1]) ? $param->sanitize($matches[$index + 1]) : (isset($_REQUEST[$param->getName()]) ? $param->sanitize($_REQUEST[$param->getName()]) : $param->getDefaultValue());
			$value = $matches[$index + 1] ?? $param->getDefaultValue();

			if (is_scalar($value))
				$pathValues = str_replace('$' . $param->getName(), $value, $pathValues);
		}

		$this->getContext()->setParams($paramsValues);
		$this->getContext()->setPath($this->path);
		$this->getContext()->setPathRegex($pathRegex);
		$this->getContext()->setPathValues($pathValues);

		$isRequestMethodValid = ($this->requestMethod === null || $this->requestMethod === $requestMethod);

		foreach ($this->parent->getMiddlewares() as $middleware)
			$middleware->forContext($this, $this->getContext(), $isRouteValid, $isRequestMethodValid);

		if ($this->getContext()->getRedirectPath() !== null)
		{
			http_response_code($this->getContext()->getRedirectCode());
			header('Location: ' . $this->resolve($this->getContext()->getRedirectPath()));
			die;
		}

		return $isRouteValid && $isRequestMethodValid;
	}

	/**
	 * Gets if sub routers are allowed.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getAllowSubRoutes(): bool
	{
		return $this->allowSubRoutes;
	}

	/**
	 * Sets if sub routes are allowed.
	 *
	 * @param bool $allowSubRoutes
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function setAllowSubRoutes(bool $allowSubRoutes): void
	{
		$this->allowSubRoutes = $allowSubRoutes;
	}

	/**
	 * Gets the context.
	 *
	 * @return RouteContext
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getContext(): RouteContext
	{
		if ($this->context === null)
			$this->context = new RouteContext($this);

		return $this->context;
	}

	/**
	 * Gets the route options.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getOptions(): array
	{
		return $this->options;
	}

	/**
	 * Gets the path.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getPath(): string
	{
		return $this->path;
	}

	/**
	 * Gets the request method.
	 *
	 * @return string|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getRequestMethod(): ?string
	{
		return $this->requestMethod;
	}

	/**
	 * Sets the request method.
	 *
	 * @param string $requestMethod
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function setRequestMethod(string $requestMethod): void
	{
		$this->requestMethod = $requestMethod;
	}

	/**
	 * Gets the parent {@see Router}.
	 *
	 * @return Router
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getParentRouter(): Router
	{
		return $this->parent;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function __debugInfo(): array
	{
		return [
			'context:' . RouteContext::class => $this->context,
			'path:string' => $this->path,
			'requestMethod:string' => $this->requestMethod
		];
	}

}
