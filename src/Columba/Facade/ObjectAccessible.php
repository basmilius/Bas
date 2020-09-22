<?php
declare(strict_types=1);

namespace Columba\Facade;

/**
 * Trait ObjectAccessible
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Facade
 * @since 1.6.0
 */
trait ObjectAccessible
{

	/**
	 * Returns the value of the given field.
	 *
	 * @param string $field
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __get(string $field)
	{
		return $this->getValue($field);
	}

	/**
	 * Returns TRUE if the given field exists.
	 *
	 * @param string $field
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __isset(string $field): bool
	{
		return $this->hasValue($field);
	}

	/**
	 * Sets the given field with the given value.
	 *
	 * @param string $field
	 * @param $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __set(string $field, $value): void
	{
		$this->setValue($field, $value);
	}

	/**
	 * Unsets the given field.
	 *
	 * @param string $field
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __unset(string $field): void
	{
		$this->unsetValue($field);
	}

}
