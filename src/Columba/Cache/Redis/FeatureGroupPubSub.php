<?php
declare(strict_types=1);

namespace Columba\Cache\Redis;

use Redis;

/**
 * Trait FeatureGroupPubSub
 *
 * @property Redis $connection
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Cache\Redis
 * @since 1.6.0
 */
trait FeatureGroupPubSub
{

	/**
	 * Subscribe to channels that match the given patterns.
	 *
	 * @param string[] $patterns
	 * @param callable $fn
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function psubscribe(array $patterns, callable $fn): void
	{
		$this->connection->psubscribe($patterns, $fn);
	}

	/**
	 * Publish the given message to the subscribers of the given channel.
	 *
	 * @param string $channel
	 * @param string $message
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function publish(string $channel, string $message): int
	{
		return $this->connection->publish($channel, $message);
	}

	/**
	 * Returns information about the Redis pub/sub system.
	 *
	 * @param string $keyword
	 * @param string|array $argument
	 *
	 * @return array|int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function pubsub(string $keyword, $argument)
	{
		return $this->connection->pubsub($keyword, $argument);
	}

	/**
	 * Unsubsribe from channels that match the given patterns.
	 *
	 * @param array|null $patterns
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function punsubscribe(?array $patterns = null): void
	{
		$this->connection->punsubscribe($patterns);
	}

	/**
	 * Subscribe to the given channels.
	 *
	 * @param string[] $channels
	 * @param callable $fn
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function subscribe(array $channels, callable $fn): void
	{
		$this->connection->subscribe($channels, $fn);
	}

	/**
	 * Unsubscribe from the given channels.
	 *
	 * @param array|null $channels
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function unsubscribe(?array $channels = null): void
	{
		$this->connection->unsubscribe($channels);
	}

}
