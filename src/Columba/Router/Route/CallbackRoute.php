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

namespace Columba\Router\Route;

use Columba\Router\RouteParam;
use Columba\Router\Router;
use Columba\Router\RouterException;
use ReflectionException;
use ReflectionMethod;

/**
 * Class CallbackRoute
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router\Route
 * @since 1.3.0
 */
final class CallbackRoute extends AbstractRoute
{

	/**
	 * @var callable
	 */
	private $callback;

	/**
	 * @var ReflectionMethod|null
	 */
	private $reflection;

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
		$this->reflection = null;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function executeImpl(bool $respond)
	{
		$params = $this->getContext()->getParams(false);
		$params['context'] = $this->getContext();

		$arguments = [];

		foreach ($this->reflection->getParameters() as $parameter)
		{
			if (isset($params[$parameter->getName()]))
				$arguments[] = $params[$parameter->getName()];
		}

		$result = $this->getReflection()->invoke($this->callback[0], ...$arguments);

		if ($respond)
			$this->respond($result);

		return $result;
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
			if ($parameter->getType() === null || !class_exists($parameter->getType()->getName()))
				$params[] = new RouteParam($parameter->getName(), $parameter->getType()->getName(), $parameter->getType()->allowsNull(), $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null);

		return $params;
	}

	/**
	 * Gets the reflection instance or creates a new one.
	 *
	 * @return ReflectionMethod
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	private function getReflection(): ReflectionMethod
	{
		try
		{
			return $this->reflection ?? $this->reflection = new ReflectionMethod($this->callback[0], $this->callback[1]);
		}
		catch (ReflectionException $err)
		{
			throw new RouterException('Could not create reflection instance.', RouterException::ERR_REFLECTION_FAILED, $err);
		}
	}

}
