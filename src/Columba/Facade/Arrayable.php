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

namespace Columba\Facade;

use ArrayAccess;

/**
 * Interface Arrayable
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Facade
 * @since 1.6.0
 */
interface Arrayable extends ArrayAccess
{

	/**
	 * Returns TRUE if an field exists.
	 *
	 * @param mixed $field
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function offsetExists($field): bool;

	/**
	 * Returns a field.
	 *
	 * @param mixed $field
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function offsetGet($field);

	/**
	 * Sets a field.
	 *
	 * @param mixed $field
	 * @param mixed $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function offsetSet($field, $value): void;

	/**
	 * Unsets a field.
	 *
	 * @param mixed $field
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function offsetUnset($field): void;

	/**
	 * Returns an array representation of the object.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function toArray(): array;

}
