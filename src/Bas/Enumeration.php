<?php
/**
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Bas package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bas;

use JsonSerializable;
use ReflectionClass;
use UnexpectedValueException;

/**
 * Class Enumeration
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas
 * @since 1.0.0
 */
abstract class Enumeration implements JsonSerializable
{

	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * @var mixed[]
	 */
	protected static $cache = [];

	/**
	 * Enumeration constructor.
	 *
	 * @param mixed $value
	 *
	 * @throws \ReflectionException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct ($value)
	{
		if (!static::isValid($value))
			throw new UnexpectedValueException("Value $value is not part of the enum " . get_called_class(), 0xFEBA007);

		$this->value = $value;
	}

	/**
	 * Returns the key.
	 *
	 * @return string
	 * @throws \ReflectionException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getKey (): string
	{
		return static::search($this->value);
	}

	/**
	 * Returns the value.
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getValue ()
	{
		return $this->value;
	}

	/**
	 * Returns TRUE if {@see $value} is a valid enum value.
	 *
	 * @param $value
	 *
	 * @return bool
	 * @throws \ReflectionException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function isValid ($value): bool
	{
		return in_array($value, static::toArray(), true);
	}

	/**
	 * Returns TRUE if {@see $key} is a valid enum key.
	 *
	 * @param string $key
	 *
	 * @return bool
	 * @throws \ReflectionException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function isValidKey (string $key): bool
	{
		$array = static::toArray();

		return isset($array[$key]);
	}

	/**
	 * Returns the key for a value.
	 *
	 * @param $value
	 *
	 * @return string|null
	 * @throws \ReflectionException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function search ($value): ?string
	{
		$key = array_search($value, static::toArray(), true);

		if ($key)
			return $key;

		return null;
	}

	/**
	 * Gets the enum values as an array.
	 *
	 * @return array
	 * @throws \ReflectionException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function toArray (): array
	{
		$class = get_called_class();

		if (!isset(static::$cache[$class]))
		{
			$reflection = new ReflectionClass($class);
			static::$cache[$class] = $reflection->getConstants();
		}

		return static::$cache[$class];
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function __callStatic (string $name, array $arguments)
	{
		$array = static::toArray();

		if (isset($array[$name]))
			return new static($array[$name]);

		throw new \BadMethodCallException("No static method or enum constant $name in Enumeration " . get_called_class(), 0xFEBA008);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function __toString (): string
	{
		return (string)$this->getValue();
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function jsonSerialize ()
	{
		return $this->getValue();
	}

}
