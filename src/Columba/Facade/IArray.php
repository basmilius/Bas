<?php
declare(strict_types=1);

namespace Columba\Facade;

use ArrayAccess;

/**
 * Interface IArray
 *
 * @package Columba\Facade
 * @author Bas Milius <bas@mili.us>
 * @since 1.4.0
 */
interface IArray extends ArrayAccess
{

	/**
	 * Returns TRUE if an field exists.
	 *
	 * @param mixed $field
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function offsetExists($field): bool;

	/**
	 * Returns a field.
	 *
	 * @param mixed $field
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function offsetGet($field);

	/**
	 * Sets a field.
	 *
	 * @param mixed $field
	 * @param mixed $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function offsetSet($field, $value): void;

	/**
	 * Unsets a field.
	 *
	 * @param mixed $field
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function offsetUnset($field): void;

	/**
	 * Returns an array representation of the object.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function toArray(): array;

}
