<?php
declare(strict_types=1);

namespace Columba\Router\Route;

use Closure;
use Columba\Router\RouteParam;
use Columba\Router\Router;
use Columba\Router\RouterException;
use ReflectionException;
use ReflectionFunction;

/**
 * Class ClosureRoute
 *
 * @author Bas Milius <bas@ideemedia.nl>
 * @package Columba\Router\Route
 * @since 1.3.1
 */
final class ClosureRoute extends AbstractRoute
{

	/**
	 * @var Closure
	 */
	private $closure;

	/**
	 * @var ReflectionFunction
	 */
	private $reflection;

	/**
	 * ClosureRoute constructor.
	 *
	 * @param Router  $parent
	 * @param string  $path
	 * @param Closure $closure
	 * @param array   $options
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.1
	 */
	public function __construct(Router $parent, string $path, Closure $closure, array $options = [])
	{
		parent::__construct($parent, $path, $options);

		$this->closure = $closure;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.1
	 */
	public final function executeImpl(bool $respond)
	{
		$params = $this->getContext()->getParams(false);
		$params['context'] = $this->getContext();

		$this->getContext()->setCallback($this->getReflection());

		$arguments = [];

		foreach ($this->reflection->getParameters() as $parameter)
		{
			if (isset($params[$parameter->getName()]))
				$arguments[] = $params[$parameter->getName()];
		}

		$result = $this->getReflection()->invoke(...$arguments);

		if ($respond && (!$this->getReflection()->hasReturnType() || $this->getReflection()->getReturnType()->getName() !== 'void'))
			$this->respond($result);

		return $result;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.1
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
	 * @return ReflectionFunction
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.1
	 */
	private function getReflection(): ReflectionFunction
	{
		try
		{
			return $this->reflection ?? $this->reflection = new ReflectionFunction($this->closure);
		}
		catch (ReflectionException $err)
		{
			throw new RouterException('Could not create reflection instance.', RouterException::ERR_REFLECTION_FAILED, $err);
		}
	}

}
