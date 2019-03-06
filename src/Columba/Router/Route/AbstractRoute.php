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

namespace Columba\Router\Route;

use Columba\Http\ResponseCode;
use Columba\Router\Response\AbstractResponse;
use Columba\Router\Response\ResponseWrapper;
use Columba\Router\RouteContext;
use Columba\Router\RouteParam;
use Columba\Router\Router;
use Columba\Router\RouterException;
use Columba\Util\A;
use Columba\Util\ServerTiming;
use Columba\Util\Stopwatch;
use Exception;

/**
 * Class AbstractRoute
 *
 * @package Columba\Router\Route
 * @author Bas Milius <bas@mili.us>
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
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function execute(): void
	{
		$result = null;

		$this->parent->onExecute($this, $this->getContext());
		$this->parent->setCurrentRoute($this);

		try
		{
			if ($this->getContext()->getResponse()[0] === null && $this->getContext()->getRedirectPath() === null)
				$this->executeImpl();

			/** @var AbstractResponse $responseImplementation */
			[$responseImplementation, $responseValue] = $this->getContext()->getResponse();

			if ($this->getContext()->getRedirectPath() === null)
			{
				if ($responseImplementation === null)
					return;

				ServerTiming::stop(Router::class, $time, Stopwatch::UNIT_SECONDS);

				$this->getContext()->setResolutionTime($time);

				http_response_code($this->getContext()->getResponseCode());
				$responseImplementation->print($this->getContext(), $responseValue);
			}
			else
			{
				$protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
				$statusCode = $this->getContext()->getResponseCode();
				$statusMessage = ResponseCode::getMessage($statusCode);

				http_response_code($statusCode);
				header("$protocol $statusCode $statusMessage");
				header('Location: ' . $this->resolve($this->getContext()->getRedirectPath()));
			}
		}
		catch (Exception $err)
		{
			$this->parent->onException($err, $this->getContext());
		}
	}

	/**
	 * Implementation for {@see AbstractRoute::execute()}.
	 *
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public abstract function executeImpl(): void;

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
		if (mb_substr($path, 0, 1) === '/' || mb_substr($path, 0, 4) === 'http')
			return $path; // No need to resolve.

		$parts = explode('/', $this->context->getFullPath() . '/' . $path);
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

		$this->getContext()->setResponse($response, $value);
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
		$this->getContext()->setPath($this->path);

		$agressiveProfiling = defined('COLUMBA_ROUTER_AGRESSIVE_PROFILING') && COLUMBA_ROUTER_AGRESSIVE_PROFILING;
		$fullPath = $this->getContext()->getFullPath(false);

		if ($agressiveProfiling)
			ServerTiming::start($fullPath . $requestMethod, "$requestMethod $fullPath", 'cpu');

		if (mb_strlen($path) > 1 && mb_substr($path, -1) === '/')
			$path = mb_substr($path, 0, -1);

		if ($path === '/index')
			$path = '/';

		$params = $this->getValidatableParams();
		$paramsValues = [];
		$pathRegex = str_replace('/*', '/(?<wildcard>.*)', $this->path);
		$pathValues = $this->path;

		foreach ($params as $param)
			$pathRegex = str_replace(['/$' . $param->getName(), '.$' . $param->getName()], $param->getRegex(), $pathRegex);

		$pathRegex = '#^' . $pathRegex . (!$this->allowSubRoutes ? '$' : '') . '#';
		$isValid = preg_match($pathRegex, $path, $matches);

		if ($isValid === false)
			throw new RouterException(sprintf("Could not compile regex for route '%s'.", $this->path), RouterException::ERR_REGEX_COMPILATION_FAILED);

		$isValid = $isValid === 1 && mb_substr($path, 0, mb_strlen($matches[0])) === $matches[0];

		foreach ($params as $index => $param)
		{
			$paramsValues[$param->getName()] = !empty($matches[$param->getName()]) ? $param->sanitize($matches[$param->getName()]) : (isset($_REQUEST[$param->getName()]) ? $param->sanitize($_REQUEST[$param->getName()]) : $param->getDefaultValue());
			$value = $matches[$param->getName()] ?? $param->getDefaultValue();

			if (is_scalar($value))
				$pathValues = str_replace('$' . $param->getName(), $value, $pathValues);
		}

		if (isset($matches['wildcard']))
		{
			$paramsValues['wildcard'] = $matches['wildcard'];
			$pathValues = str_replace('/*', '/' . $matches['wildcard'], $pathValues);
		}

		$this->getContext()->setParams($paramsValues);
		$this->getContext()->setPathRegex($pathRegex);
		$this->getContext()->setPathValues($pathValues);

		if (!($this->requestMethod === null || $this->requestMethod === $requestMethod))
			return false;

		try
		{
			foreach ($this->parent->getMiddlewares() as $middleware)
				$middleware->forContext($this, $this->getContext(), $isValid);

			return $isValid;
		}
		catch (Exception $err)
		{
			$this->parent->onException($err, $this->getContext());

			return false;
		}
		finally
		{
			if ($agressiveProfiling)
				ServerTiming::stop($fullPath . $requestMethod);
		}
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
