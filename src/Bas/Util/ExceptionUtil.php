<?php
/**
 * This file is part of the Bas package.
 *
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bas\Util;

use Exception;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use Throwable;

/**
 * Class ExceptionUtil
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Util
 * @since 1.0.0
 */
final class ExceptionUtil
{

	/**
	 * Converts a {@see Throwable} to an array.
	 *
	 * @param Throwable $exception
	 *
	 * @return array
	 * @throws \ReflectionException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function exceptionToArray (Throwable $exception): array
	{
		$class = get_class($exception);
		$classReflection = new ReflectionClass($class);

		$code = $exception->getCode();
		$codeName = 'ERR_UNKNOWN';
		$file = $exception->getFile();
		$line = $exception->getLine();
		$message = $exception->getMessage();

		foreach ($classReflection->getConstants() as $constant => $value)
			if ($value === $code)
				$codeName = $constant;

		$stacktrace = array_map([ExceptionUtil::class, 'traceItemToArray'], $exception->getTrace());

		return [
			'code' => $code,
			'code_hex' => '0x' . strtoupper(dechex($code)),
			'code_name' => $codeName,
			'file' => $file,
			'line' => $line,
			'message' => $message,
			'stacktrace' => $stacktrace
		];
	}

	/**
	 * Converts a {@see Throwable} to an array with the previous exceptions.
	 *
	 * @param Throwable $exception
	 *
	 * @return array
	 * @throws \ReflectionException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function exceptionToExceptions (Throwable $exception): array
	{
		$exceptions = [];

		$current = $exception = new Exception('', 0, $exception);

		while (($previous = $current->getPrevious()) !== null)
			$exceptions[] = self::exceptionToArray($current = $previous);

		return $exceptions;
	}

	/**
	 * Converts a trace item to an array.
	 *
	 * @param array $item
	 *
	 * @return array
	 * @throws \ReflectionException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function traceItemToArray (array $item): array
	{
		$callable = null;
		$callableReflection = null;

		$isFunction = isset($item['function']) && !isset($item['class']);
		$isInternal = ($isFunction && !function_exists($item['function']));
		$isMethod = isset($item['function'], $item['class']);

		$params = null;

		if ($isFunction || $isMethod)
		{
			$callable = $isFunction ? $item['function'] : [$item['class'], $item['function']];

			if (!$isInternal)
			{
				$callableReflection = $isFunction ? new ReflectionFunction($callable) : new ReflectionMethod($callable[0], $callable[1]);

				$params = [];
				$parameters = $callableReflection->getParameters();

				for ($i = 0; $i < $callableReflection->getNumberOfParameters(); $i++)
				{
					$isValueGiven = isset($item['args'][$i]);

					$param = $parameters[$i];
					$value = $item['args'][$i] ?? null;

					if ($param->isDefaultValueAvailable() && !$isValueGiven)
						$value = $param->getDefaultValue();

					$type = null;

					if (is_scalar($value))
					{
						$type = gettype($value);
					}
					else if (is_array($value))
					{
//						if (count($value) === 2 && is_callable($value))
//						{
//							$type = $value[0] . '::' . $value[1];
//						}
//						else if (count($value) === 1 && is_callable($value))
//						{
//							$type = $value[0];
//						}
//						else
//						{
							$type = 'array(' . count($value) . ')';
//						}
					}

					$params[] = [
						'name' => $param->getName(),
						'type' => $type,
						'value' => $value
					];
				}
			}
		}

		return [
			'callable' => $callable,
			'callable_reflection' => $callableReflection,
			'file' => $item['file'] ?? null,
			'line' => $item['line'] ?? null,
			'is_function' => $isFunction,
			'is_internal' => $isInternal,
			'is_method' => $isMethod,
			'params' => $params
		];
	}

}
