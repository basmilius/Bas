<?php
declare(strict_types=1);

namespace Columba\Facade;

/**
 * Trait ArrayAccessible
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Facade
 * @since 1.6.0
 */
trait ArrayAccessible
{

	/**
	 * Returns TRUE if the given field exists.
	 *
	 * @param mixed $field
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function offsetExists($field): bool
	{
		return $this->hasValue($field);
	}

	/**
	 * Returns the value of the given field.
	 *
	 * @param mixed $field
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function offsetGet($field)
	{
		return $this->getValue($field);
	}

	/**
	 * Sets the given field with the given value.
	 *
	 * @param mixed $field
	 * @param mixed $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function offsetSet($field, $value): void
	{
		$this->setValue($field, $value);
	}

	/**
	 * Unsets the given field.
	 *
	 * @param mixed $field
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function offsetUnset($field): void
	{
		$this->unsetValue($field);
	}

}
