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

namespace Columba\Router\Route;

use Columba\Facade\Debuggable;
use Columba\Http\ResponseCode;
use Columba\Router\Context;
use Columba\Router\Middleware\AbstractMiddleware;
use Columba\Router\Response\AbstractResponse;
use Columba\Router\Response\ResponseWrapper;
use Columba\Router\RouteParam;
use Columba\Router\Router;
use Columba\Router\RouterException;
use Columba\Router\SubRouter;
use Columba\Util\ServerTiming;
use Columba\Util\Stopwatch;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use function array_flip;
use function array_keys;
use function array_pop;
use function class_exists;
use function count;
use function explode;
use function get_class;
use function header;
use function http_response_code;
use function implode;
use function is_scalar;
use function is_subclass_of;
use function mb_strlen;
use function mb_substr;
use function preg_match;
use function rtrim;
use function sprintf;
use function str_replace;
use function strtr;

/**
 * Class AbstractRoute
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router\Route
 * @since 1.3.0
 */
abstract class AbstractRoute implements Debuggable
{

	private bool $allowSubRoutes = false;
	private ?Context $context = null;
	private array $options = [];

	/** @var AbstractMiddleware[] */
	private array $middlewares = [];

	/** @var string[] */
	private array $requestMethods;

	/**
	 * AbstractRoute constructor.
	 *
	 * @param Router $parent
	 * @param string[] $requestMethods
	 * @param string $path
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(private Router $parent, array $requestMethods, private string $path)
	{
		$this->requestMethods = array_flip($requestMethods);
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

		try
		{
			$this->parent->onExecute($this, $context);

			$rootRouter = $this->parent instanceof SubRouter ? $this->parent->getRootRouter() : $this->parent;
			$rootRouter->setCurrentRoute($this);

			if ($context->getResponse()[0] === null && $context->getRedirectPath() === null)
				$this->executeImpl();

			/** @var AbstractResponse $response */
			[$response, $responseValue] = $context->getResponse();

			$protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
			$statusCode = $context->getResponseCode();
			$statusMessage = ResponseCode::getMessage($statusCode);

			if ($context->getRedirectPath() === null)
			{
				if (!headers_sent())
				{
					http_response_code($statusCode);
					header("$protocol $statusCode $statusMessage");
				}

				if ($response === null)
					return;

				ServerTiming::stop(Router::class, $time, Stopwatch::UNIT_SECONDS);

				$context->setResolutionTime($time);

				$response->print($context, $responseValue);
			}
			else
			{
				ServerTiming::stop(Router::class, $time, Stopwatch::UNIT_SECONDS);

				if (headers_sent())
					return;

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
	 * Adds a {@see AbstractRoute} to use.
	 *
	 * @param string $middleware
	 * @param mixed ...$arguments
	 *
	 * @return $this
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function middleware(string $middleware, ...$arguments): self
	{
		if (!class_exists($middleware))
			throw new RouterException(sprintf('Middleware %s not found.', $middleware), RouterException::ERR_MIDDLEWARE_NOT_FOUND);

		if (!is_subclass_of($middleware, AbstractMiddleware::class))
			throw new RouterException(sprintf('Class %s needs to extend from %s to be a valid middleware.', $middleware, AbstractMiddleware::class), RouterException::ERR_MIDDLEWARE_INVALID);

		$this->middlewares[] = new $middleware($this->parent, ...$arguments);

		return $this;
	}

	/**
	 * Sets the options of the route.
	 *
	 * @param array $options
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function options(array $options): self
	{
		$this->options = $options;

		return $this;
	}

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
		if ($path[0] === '/' || mb_substr($path, 0, 4) === 'http')
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
				array_pop($result);
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
			$response = $this->parent->getResponse();
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
		$context = $this->getContext();
		$context->setPath($this->path);

		$path = $path !== '/' ? rtrim($path, '/') : $path;
		$params = $this->getValidatableParams();
		$paramsValues = [];
		$pathRegex = strtr($this->path, ['/*' => '/(?<wildcard>.*)']);
		$pathValues = $this->path;

		foreach ($params as $param)
		{
			$name = $param->getName();
			$regex = $param->getRegex();

			$pathRegex = strtr($pathRegex, [
				'/$' . $name => $regex,
				'.$' . $name => $regex
			]);
		}

		$pathRegex = '#^' . $pathRegex . (!$this->allowSubRoutes ? '$' : '') . '#';
		$isValid = preg_match($pathRegex, $path, $matches);

		if ($isValid === false)
			throw new RouterException(sprintf("Could not compile regex for route '%s'.", $this->path), RouterException::ERR_REGEX_COMPILATION_FAILED);

		$isValid = $isValid === 1 && mb_substr($path, 0, mb_strlen($matches[0])) === $matches[0];

		foreach ($params as $index => $param)
		{
			$name = $param->getName();
			$match = $matches[$name] ?? null;

			$paramsValues[$name] = (!empty($match) || $match === '0') ? $param->sanitize($match) : (isset($_REQUEST[$name]) ? $param->sanitize($_REQUEST[$name]) : $param->getDefaultValue());
			$value = $match ?? $param->getDefaultValue();

			if (is_scalar($value))
				$pathValues = str_replace('$' . $name, (string)$value, $pathValues);
		}

		if (isset($matches['wildcard']))
		{
			$paramsValues['wildcard'] = $matches['wildcard'];
			$pathValues = strtr($pathValues, '/*', '/' . $matches['wildcard']);
		}

		$context->setParams($paramsValues);
		$context->setPathRegex($pathRegex);
		$context->setPathValues($pathValues);

		if (!$isValid || !(empty($this->requestMethods) || isset($this->requestMethods[$requestMethod])))
			return false;

		$middleware = null;

		try
		{
			$routerMiddlewares = $this->parent->getMiddlewares();

			foreach ($routerMiddlewares as $middleware)
				$middleware->forContext($this, $context, $isValid);

			foreach ($this->middlewares as $middleware)
				$middleware->forContext($this, $context, $isValid);

			return $isValid;
		}
		catch (Exception $err)
		{
			if ($middleware !== null)
				$this->parent->onException(new RouterException(sprintf("Middleware '%s' threw an exception while executing '%s'.", get_class($middleware), $context->getFullPath()), RouterException::ERR_MIDDLEWARE_THREW_EXCEPTION, $err), $context);
			else
				$this->parent->onException(new RouterException(sprintf("Unkown exception while executing '%s'.", $context->getFullPath()), RouterException::ERR_MIDDLEWARE_THREW_EXCEPTION, $err), $context);

			return false;
		}
	}

	/**
	 * Gets the context.
	 *
	 * @return Context
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getContext(): Context
	{
		return $this->context ?? $this->context = new Context($this);
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
	#[ArrayShape([
		'path' => 'string',
		'requestMethods' => 'string',
		'middlewares' => '\Columba\Router\Middleware\AbstractMiddleware[]'
	])]
	public function __debugInfo(): array
	{
		return [
			'path' => $this->path,
			'requestMethods' => implode(', ', array_keys($this->requestMethods)),
			'middlewares' => $this->middlewares
		];
	}

}
