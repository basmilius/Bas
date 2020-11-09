<?php
declare(strict_types=1);

namespace Columba\Collection;

use function implode;
use function is_string;
use function sprintf;

/**
 * Class StringArrayList
 *
 * @extends ArrayList<string>
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Collection
 * @since 1.6.0
 */
class StringArrayList extends ArrayList
{

	/**
	 * Joins the strings together using the given separator.
	 *
	 * @param string $separator
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function join(string $separator = ', '): string
	{
		return implode($separator, $this->items);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected static function validateItem($item): void
	{
		if (!is_string($item))
		{
			throw new CollectionException(sprintf('%s only accepst strings.', self::class), CollectionException::ERR_INVALID_TYPE);
		}
	}

}
