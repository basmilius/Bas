<?php
declare(strict_types=1);

namespace Columba\Facade;

/**
 * Trait EasyAccessible
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Facade
 * @since 1.6.0
 */
trait EasyAccessible
{

	/**
	 * Gets a value.
	 *
	 * @param string $field
	 * @param mixed $defaultValue
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function get(string $field, $defaultValue = null)
	{
		return $this->getValue($field) ?? $defaultValue;
	}

	/**
	 * Returns TRUE if a value exists.
	 *
	 * @param string $field
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function has(string $field): bool
	{
		return $this->hasValue($field);
	}

	/**
	 * Sets a value.
	 *
	 * @param string $field
	 * @param mixed $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function set(string $field, $value): void
	{
		$this->setValue($field, $value);
	}

	/**
	 * Unsets a value.
	 *
	 * @param string $field
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function unset(string $field): void
	{
		$this->unsetValue($field);
	}

}
