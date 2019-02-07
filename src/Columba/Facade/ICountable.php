<?php
declare(strict_types=1);

namespace Columba\Facade;

use Countable;

/**
 * Interface ICountable
 *
 * @package Columba\Facade
 * @author Bas Milius <bas@mili.us>
 * @since 1.4.0
 */
interface ICountable extends Countable
{

	/**
	 * Returns the amount of elements in an object.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function count(): int;

}
