<?php
declare(strict_types=1);

namespace Columba\Util;

use ReflectionClass;
use ReflectionException;

/**
 * Class ReflectionUtil
 *
 * @package Columba\Util
 * @author Bas Milius <bas@mili.us>
 * @since 1.4.0
 */
final class ReflectionUtil
{

	/**
	 * Finds the name of a constant by value.
	 *
	 * @param string $class
	 * @param mixed  $value
	 * @param mixed  $constant
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

}
