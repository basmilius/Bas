<?php
declare(strict_types=1);

namespace Columba\Collection;

use function array_sum;
use function is_int;
use function sprintf;

/**
 * Class IntArrayList
 *
 * @extends ArrayList<int>
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Collection
 * @since 1.6.0
 */
class IntArrayList extends ArrayList
{

	/**
	 * Sums the items of the ArrayList.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function sum(): int
	{
		return (int)array_sum($this->items);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected static function validateItem($item): void
	{
		if (!is_int($item))
		{
			throw new CollectionException(sprintf('%s only accepts integers.', self::class), CollectionException::ERR_INVALID_TYPE);
		}
	}

}
