<?php
declare(strict_types=1);

namespace Columba\Cache\Redis;

use Columba\Error\ColumbaException;

/**
 * Class RedisCacheException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Cache\Redis
 * @since 1.6.0
 */
final class RedisCacheException extends ColumbaException
{

	public const ERR_DATABASE_SELECT_FAILED = 1;
	public const ERR_INVALID_CALL = 2;

}