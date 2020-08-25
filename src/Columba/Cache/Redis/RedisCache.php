<?php
declare(strict_types=1);

namespace Columba\Cache\Redis;

use Redis;

/**
 * Class RedisCache
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Cache\Redis
 * @since 1.6.0
 */
class RedisCache
{

	use FeatureGroupKeys;
	use FeatureGroupSets;
	use FeatureGroupStrings;

	protected Redis $connection;

	protected string $prefix;
	protected string $host;
	protected int $port;
	protected float $timeout;

	/**
	 * RedisCache constructor.
	 *
	 * @param string $prefix
	 * @param string $host
	 * @param int $port
	 * @param float $timeout
	 * @param bool $connect
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(string $prefix, string $host = '127.0.0.1', int $port = 6379, float $timeout = 0.0, bool $connect = true)
	{
		$this->connection = new Redis();

		$this->prefix = $prefix;
		$this->host = $host;
		$this->port = $port;
		$this->timeout = $timeout;

		if ($connect)
			$this->connect();
	}

	/**
	 * Gets the host.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getHost(): string
	{
		return $this->host;
	}

	/**
	 * Gets the port.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getPort(): int
	{
		return $this->port;
	}

	/**
	 * Gets the prefix.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getPrefix(): string
	{
		return $this->prefix;
	}

	/**
	 * Gets the timeout.
	 *
	 * @return float
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getTimeout(): float
	{
		return $this->timeout;
	}

	/**
	 * Connect to the Redis server.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function connect(): bool
	{
		return $this->connection->connect($this->host, $this->port, $this->timeout);
	}

	/**
	 * Returns TRUE if we're connected to a Redis server.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function isConnected(): bool
	{
		return $this->connection->isConnected();
	}

	/**
	 * Selects the given database.
	 *
	 * @param int $databaseId
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function selectDatabase(int $databaseId): void
	{
		if ($this->connection->select($databaseId) === false)
			throw new RedisCacheException(sprintf('Could not select database with id %d.', $databaseId), RedisCacheException::ERR_DATABASE_SELECT_FAILED);
	}

	/**
	 * Returns a {@see RedisTaggedCache} instance.
	 *
	 * @param array $tags
	 *
	 * @return RedisTaggedCache
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function tags(array $tags): RedisTaggedCache
	{
		return new RedisTaggedCache($this, $tags);
	}

}