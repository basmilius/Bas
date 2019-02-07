<?php
declare(strict_types=1);

namespace Columba\Facade;

use Iterator;

/**
 * Interface IIterator
 *
 * @package Columba\Facade
 * @author Bas Milius <bas@mili.us>
 * @since 1.4.0
 */
interface IIterator extends Iterator
{

	/**
	 * Returns the current item.
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function current();

	/**
	 * Returns the key of the current item.
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function key();

	/**
	 * Move to the next item.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function next(): void;

	/**
	 * Rewind to the first item.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function rewind(): void;

	/**
	 * Checks if the current position is valid.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function valid(): bool;

}
