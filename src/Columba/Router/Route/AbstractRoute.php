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

use Columba\Http\RequestMethod;
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
	private $allowSubRoutes = false;

	/**
	 * @var RouteContext
	 */
	private $context = null;

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
	 * @param string requestMethod
	 * @param string $path
	 * @param array  $options
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(Router $parent, string $requestMethod, string $path, array $options = [])
	{
		$this->options = $options;
		$this->requestMethod = $requestMethod;
		$this->path = $path;
		$this->parent = $parent;
	}

	/**
	 * Marks the route als subrouter.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function allowSubRoutes(): void
	{
		$this->allowSubRoutes = true;
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
		$context = $this->getContext();
		$result = null;

		$this->parent->onExecute($this, $this->getContext());
		$this->parent->setCurrentRoute($this);

		try
		{
			if ($context->getResponse()[0] === null && $context->getRedirectPath() === null)
				$this->executeImpl();

			/** @var AbstractResponse $responseImplementation */
			[$responseImplementation, $responseValue] = $context->getResponse();

			if ($context->getRedirectPath() === null)
			{
				if ($responseImplementation === null)
					return;

				ServerTiming::stop(Router::class, $time, Stopwatch::UNIT_SECONDS);

				$context->setResolutionTime($time);

				http_response_code($context->getResponseCode());
				$responseImplementation->print($context, $responseValue);
			}
			else
			{
				$protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
				$statusCode = $context->getResponseCode();
				$statusMessage = ResponseCode::getMessage($statusCode);

				ServerTiming::stop(Router::class, $time, Stopwatch::UNIT_SECONDS);

				http_response_code($statusCode);
				header("$protocol $statusCode $statusMessage");
				header('Location: ' . $this->resolve($context->getRedirectPath()));
			}
		}
		catch (Exception $err)
		{
			$this->parent->onException($err, $context);
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

		if (!$isValid || !($this->requestMethod === RequestMethod::NULL || $this->requestMethod === $requestMethod))
			return false;

		$middleware = null;

		try
		{
			foreach ($this->parent->getMiddlewares() as $middleware)
				$middleware->forContext($this, $this->getContext(), $isValid);

			return $isValid;
		}
		catch (Exception $err)
		{
			if ($middleware !== null)
				$this->parent->onException(new RouterException(sprintf("Middleware '%s' threw an exception while executing '%s'.", get_class($middleware), $this->getContext()->getFullPath()), RouterException::ERR_MIDDLEWARE_THREW_EXCEPTION, $err), $this->getContext());
			else
				$this->parent->onException(new RouterException(sprintf("Unkown exception while executing '%s'.", $this->getContext()->getFullPath()), RouterException::ERR_MIDDLEWARE_THREW_EXCEPTION, $err), $this->getContext());

			return false;
		}
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
