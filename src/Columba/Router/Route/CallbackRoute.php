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

use Closure;
use Columba\Router\RouteParam;
use Columba\Router\Router;
use Columba\Router\RouterException;
use ReflectionException;
use ReflectionFunction;
use ReflectionNamedType;
use function class_exists;

/**
 * Class CallbackRoute
 *
 * @package Columba\Router\Route
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.0
 */
class CallbackRoute extends AbstractRoute
{

	protected Closure $callback;
	protected ?ReflectionFunction $reflection = null;

	/**
	 * CallbackRoute constructor.
	 *
	 * @param Router   $parent
	 * @param string[] $requestMethods
	 * @param string   $path
	 * @param Closure  $callback
	 * @param array    $options
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(Router $parent, array $requestMethods, string $path, Closure $callback, array $options = [])
	{
		parent::__construct($parent, $requestMethods, $path, $options);

		$this->callback = $callback;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function executeImpl(): void
	{
		$arguments = [];
		$params = $this->getContext()->getParams();
		$reflection = $this->getReflection();

		$this->getContext()->setCallback($reflection);

		foreach ($reflection->getParameters() as $parameter)
		{
			if (isset($params[$parameter->getName()]))
				$arguments[] = $params[$parameter->getName()];
			else if ($parameter->allowsNull())
				$arguments[] = null;
		}

		if (!$reflection->hasReturnType() || $reflection->getReturnType()->getName() !== 'void')
			$this->respond($reflection->invoke(...$arguments));
		else
			$reflection->invoke(...$arguments);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getValidatableParams(): array
	{
		try
		{
			$params = [];
			$parameters = $this->getReflection()->getParameters();

			foreach ($parameters as $parameter)
			{
				/** @var ReflectionNamedType $type */
				$type = $parameter->getType();

				if ($type !== null && !class_exists($type->getName()))
					$params[] = new RouteParam($parameter->getName(), $type->getName(), $type->allowsNull(), $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null);
			}

			return $params;
		}
		catch (ReflectionException $err)
		{
			throw new RouterException('Could not get parameters due to a reflection error.', RouterException::ERR_REFLECTION_FAILED, $err);
		}
	}

	/**
	 * Gets the reflection instance or creates a new one.
	 *
	 * @return ReflectionFunction
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getReflection(): ReflectionFunction
	{
		if ($this->reflection !== null)
			return $this->reflection;

		try
		{
			return $this->reflection = new ReflectionFunction($this->callback);
		}
		catch (ReflectionException $err)
		{
			throw new RouterException('Could not create reflection instance.', RouterException::ERR_REFLECTION_FAILED, $err);
		}
	}

}
