<?php
declare(strict_types=1);

namespace Columba\Cache\Redis;

use Redis;

/**
 * Class FeatureGroupServer
 *
 * @property Redis $connection
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Cache\Redis
 * @since 1.6.0
 */
trait FeatureGroupServer
{

	/**
	 * Removes all information from all databases.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function flushAll(): bool
	{
		return $this->connection->flushAll();
	}

	/**
	 * Removes all information from the current database.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function flushDatabase(): bool
	{
		return $this->connection->flushDB();
	}

}
