<?php
declare(strict_types=1);

namespace Columba\Collection;

use Columba\Facade\Arrayable;
use Traversable;
use function iterator_to_array;

/**
 * Class CollectionUtils
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Collection
 * @since 1.6.0
 */
final class CollectionUtils
{

	/**
	 * Ensures an array.
	 *
	 * @param iterable $items
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function ensureArray(iterable $items): array
	{
		if ($items instanceof ArrayList)
		{
			return $items->all();
		}

		if ($items instanceof Arrayable)
		{
			return $items->toArray();
		}

		if ($items instanceof Traversable)
		{
			$items = iterator_to_array($items);
		}

		return $items;
	}

}
