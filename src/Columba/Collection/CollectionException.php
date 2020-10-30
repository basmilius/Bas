<?php
declare(strict_types=1);

namespace Columba\Collection;

use Columba\Error\ColumbaException;

/**
 * Class CollectionException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Collection
 * @since 1.6.0
 */
class CollectionException extends ColumbaException
{

	public const ERR_NON_COLLECTION = 1;
	public const ERR_INVALID_TYPE = 2;

}
