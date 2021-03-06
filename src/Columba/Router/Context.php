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

use Columba\Router\Response\AbstractResponse;
use Columba\Router\Route\AbstractRoute;
use Columba\Router\Route\LazyRouterRoute;
use Columba\Router\Route\RouterRoute;
use ReflectionFunctionAbstract;
use function array_merge;

/**
 * Class Context
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router
 * @since 1.3.0
 */
final class Context
{

	private AbstractRoute $route;
	private ?ReflectionFunctionAbstract $callback = null;
	private array $params = [];
	private ?Context $parent = null;
	private ?string $path = null;
	private ?string $pathRegex = null;
	private ?string $pathValues = null;
	private ?string $redirectPath = null;
	private float $resolutionTime = -1;
	private ?AbstractResponse $responseClass = null;
	private int $responseCode = 200;

	/** @var mixed */
	private $responseValue = null;

	/**
	 * Context constructor.
	 *
	 * @param AbstractRoute $route
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(AbstractRoute $route)
	{
		$this->route = $route;
	}

	/**
	 * Gets the full path to the {@see AbstractRoute}.
	 *
	 * @param bool $values
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getFullPath(bool $values = true): string
	{
		$current = $this;
		$path = ($values ? $this->pathValues : $this->path);

		if ($path === '/' && $current->parent !== null)
			$path = '';

		while (($current = $current->parent) !== null)
			if (($prepend = ($values ? $current->pathValues : $current->path)) !== '/')
				$path = $prepend . $path;

		return $path ?? 'NULL';
	}

	/**
	 * Gets the response implementation based on the current route context.
	 *
	 * @return AbstractResponse
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function getResponseImplementation(): AbstractResponse
	{
		$route = $this->route;

		if ($route instanceof RouterRoute || $route instanceof LazyRouterRoute)
			return $route->getRouter()->getResponse();

		return $route->getParentRouter()->getResponse();
	}

	/**
	 * Redirects our {@see Context} to something else.
	 *
	 * @param string $redirectPath
	 * @param int $responseCode
	 * @param mixed $returnValue
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function redirect(string $redirectPath, int $responseCode = 302, $returnValue = '')
	{
		$this->setResponseCode($responseCode);
		$this->redirectPath = $redirectPath;

		return $returnValue;
	}

	/**
	 * Resolves a path relative to this route.
	 *
	 * @param string $path
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function resolve(string $path): string
	{
		return $this->route->resolve($path);
	}

	/**
	 * Gets the associated {@see AbstractRoute}.
	 *
	 * @return AbstractRoute
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getRoute(): AbstractRoute
	{
		return $this->route;
	}

	/**
	 * Gets the callback.
	 *
	 * @return ReflectionFunctionAbstract|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.1
	 */
	public final function getCallback(): ?ReflectionFunctionAbstract
	{
		return $this->callback;
	}

	/**
	 * Sets the callback.
	 *
	 * @param ReflectionFunctionAbstract $callback
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.1
	 * @internal
	 */
	public final function setCallback(ReflectionFunctionAbstract $callback): void
	{
		$this->callback = $callback;
	}

	/**
	 * Gets the resolution time.
	 *
	 * @return float
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function getResolutionTime(): float
	{
		return $this->resolutionTime;
	}

	/**
	 * Sets the resolution time.
	 *
	 * @param float $resolutionTime
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 * @internal
	 */
	public final function setResolutionTime(float $resolutionTime): void
	{
		$this->resolutionTime = $resolutionTime;
	}

	/**
	 * Gets the response.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function getResponse(): array
	{
		return [$this->responseClass, $this->responseValue];
	}

	/**
	 * Sets the response.
	 *
	 * @param AbstractResponse $responseClass
	 * @param mixed $responseValue
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function setResponse(AbstractResponse $responseClass, $responseValue): void
	{
		$this->responseClass = $responseClass;
		$this->responseValue = $responseValue;
	}

	/**
	 * Gets the response code.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function getResponseCode(): int
	{
		return $this->responseCode;
	}

	/**
	 * Sets the response code.
	 *
	 * @param int $responseCode
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function setResponseCode(int $responseCode): void
	{
		$this->responseCode = $responseCode;

		if ($this->parent !== null)
			$this->parent->setResponseCode($responseCode);
	}

	/**
	 * Adds a custom param.
	 *
	 * @param string $name
	 * @param mixed $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function addParam(string $name, $value): void
	{
		$this->params[$name] = $value;
	}

	/**
	 * Gets a {@see AbstractRoute} param.
	 *
	 * @param string $name
	 * @param bool $includeParent
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getParam(string $name, bool $includeParent = true)
	{
		$params = $this->getParams($includeParent);

		return $params[$name] ?? null;
	}

	/**
	 * Gets the {@see AbstractRoute} params.
	 *
	 * @param bool $includeParent
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getParams(bool $includeParent = true): array
	{
		$params = $this->params;

		if ($includeParent && $this->parent !== null)
			$params = array_merge($params, $this->parent->getParams());

		foreach ($this->getRoute()->getParentRouter()->getGlobals() as $name => $value)
			$params[$name] = $value;

		$params['context'] = $this;

		return $params;
	}

	/**
	 * Sets the {@see AbstractRoute} params.
	 *
	 * @param array $params
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function setParams(array $params): void
	{
		$this->params = $params;
	}

	/**
	 * Gets the parent {@see Context}.
	 *
	 * @return Context|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getParent(): ?Context
	{
		return $this->parent;
	}

	/**
	 * Sets the parent {@see Context}.
	 *
	 * @param Context $parent
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 * @internal
	 */
	public final function setParent(Context $parent): void
	{
		$this->parent = $parent;
	}

	/**
	 * Gets the path.
	 *
	 * @return string|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getPath(): ?string
	{
		return $this->path;
	}

	/**
	 * Sets the path.
	 *
	 * @param $path
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 * @internal
	 */
	public final function setPath($path): void
	{
		$this->path = $path;
	}

	/**
	 * Gets the regex used to check if the {@see AbstractRoute} was a match.
	 *
	 * @return string|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getPathRegex(): ?string
	{
		return $this->pathRegex;
	}

	/**
	 * Sets the regex that is used to check if the {@see AbstractRoute} is a match.
	 *
	 * @param string $pathRegex
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 * @internal
	 */
	public final function setPathRegex(string $pathRegex): void
	{
		$this->pathRegex = $pathRegex;
	}

	/**
	 * Gets the path with values.
	 *
	 * @return string|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getPathValues(): ?string
	{
		return $this->pathValues;
	}

	/**
	 * Sets the path with values.
	 *
	 * @param string $pathValues
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 * @internal
	 */
	public final function setPathValues(string $pathValues): void
	{
		$this->pathValues = $pathValues;
	}

	/**
	 * Gets the redirect path.
	 *
	 * @return string|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getRedirectPath(): ?string
	{
		return $this->redirectPath;
	}

}
