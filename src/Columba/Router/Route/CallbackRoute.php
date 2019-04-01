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

use Columba\Router\RouteParam;
use Columba\Router\Router;
use Columba\Router\RouterException;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

/**
 * Class CallbackRoute
 *
 * @package Columba\Router\Route
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.0
 */
class CallbackRoute extends AbstractRoute
{

	/**
	 * @var callable
	 */
	protected $callback;

	/**
	 * @var ReflectionMethod|null
	 */
	protected $reflection = null;

	/**
	 * CallbackRoute constructor.
	 *
	 * @param Router   $parent
	 * @param string   $path
	 * @param callable $callback
	 * @param array    $options
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(Router $parent, string $path, callable $callback, array $options = [])
	{
		parent::__construct($parent, $path, $options);

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
			$this->respond($this->invoke($this->callback, ...$arguments));
		else
			$this->invoke($this->callback, ...$arguments);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getValidatableParams(): array
	{
		$params = [];
		$parameters = $this->getReflection()->getParameters();

		foreach ($parameters as $parameter)
			if ($parameter->getType() !== null && !class_exists($parameter->getType()->getName()))
				$params[] = new RouteParam($parameter->getName(), $parameter->getType()->getName(), $parameter->getType()->allowsNull(), $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null);

		return $params;
	}

	/**
	 * Gets the reflection instance or creates a new one.
	 *
	 * @return ReflectionFunction|ReflectionMethod
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getReflection(): ReflectionFunctionAbstract
	{
		if ($this->reflection !== null)
			return $this->reflection;

		try
		{
			if (is_array($this->callback) && is_callable($this->callback))
				return $this->reflection = new ReflectionMethod($this->callback[0], $this->callback[1]);
			else
				return $this->reflection = new ReflectionFunction($this->callback);
		}
		catch (ReflectionException $err)
		{
			throw new RouterException('Could not create reflection instance.', RouterException::ERR_REFLECTION_FAILED, $err);
		}
	}

	/**
	 * Invokes the callback.
	 *
	 * @param callable $callback
	 * @param mixed    ...$arguments
	 *
	 * @return mixed
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	protected function invoke(callable $callback, ...$arguments)
	{
		if (is_array($this->callback) && is_callable($this->callback))
			return $this->getReflection()->invoke($callback[0], ...$arguments);
		else
			return $this->getReflection()->invoke(...$arguments);
	}

}
