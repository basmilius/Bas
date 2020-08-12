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

namespace Columba\Util;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use function get_class;
use function gettype;

/**
 * Class ReflectionUtil
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Util
 * @since 1.4.0
 */
final class ReflectionUtil
{

	/**
	 * Finds the name of a constant by value.
	 *
	 * @param string $class
	 * @param mixed $value
	 * @param mixed $constant
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function findConstant(string $class, $value, &$constant = null): bool
	{
		try
		{
			$classReflection = new ReflectionClass($class);

			foreach ($classReflection->getConstants() as $name => $val)
			{
				if ($val !== $value)
					continue;

				$constant = $name;

				return true;
			}
		}
		catch (ReflectionException $err)
		{
		}

		return false;
	}

	/**
	 * Gets the name of a closure.
	 *
	 * @param Closure $closure
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function getClosureName(Closure $closure): string
	{
		try
		{
			$ref = new ReflectionFunction($closure);

			return get_class($ref->getClosureThis()) . '::' . $ref->getName();
		}
		catch (ReflectionException $err)
		{
			return '{closure}';
		}
	}

	/**
	 * Gets parameters of a method or function as an associative array.
	 *
	 * @param ReflectionFunctionAbstract $methodOrFunction
	 *
	 * @return ReflectionParameter[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public static function getParameters(ReflectionFunctionAbstract $methodOrFunction): array
	{
		$parameters = [];

		foreach ($methodOrFunction->getParameters() as $parameter)
			$parameters[$parameter->getName()] = $parameter;

		return $parameters;
	}

	/**
	 * Gets the type of a value.
	 *
	 * @param $value
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function getType($value): string
	{
		$type = gettype($value);

		if ($type !== 'object')
			return $type;

		return get_class($value);
	}

}
