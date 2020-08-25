<?php
declare(strict_types=1);

namespace Columba\Cache\Redis;

/**
 * Class RedisTaggedCache
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Cache\Redis
 * @since 1.6.0
 */
class RedisTaggedCache
{

	protected RedisCache $cache;
	protected string $scope;
	protected array $tags;

	/**
	 * RedisTaggedCache constructor.
	 *
	 * @param RedisCache $cache
	 * @param array $tags
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(RedisCache $cache, array $tags)
	{
		if (empty($tags))
			throw new RedisCacheException('Tagged cache should at least have one tag.', RedisCacheException::ERR_INVALID_CALL);

		$this->cache = $cache;
		$this->tags = $tags;
		$this->scope = \implode('|', $this->tags);
	}

	/**
	 * Gets the cache instance.
	 *
	 * @return RedisCache
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getCache(): RedisCache
	{
		return $this->cache;
	}

	/**
	 * Gets the scope.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getScope(): string
	{
		return $this->scope;
	}

	/**
	 * Returns the tags.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getTags(): array
	{
		return $this->tags;
	}

	/**
	 * Returns the given key with tags embedded.
	 *
	 * @param string $key
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function key(string $key): string
	{
		return $this->keyRaw(\sha1($this->scope), $key);
	}

	/**
	 * Generates a raw key.
	 *
	 * @param string ...$parts
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function keyRaw(string ...$parts): string
	{
		\array_unshift($parts, $this->cache->getPrefix());

		return \implode(':', $parts);
	}

	/**
	 * Links the tags to the given key.
	 *
	 * @param string $key
	 * @param int $ttl
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function linkTags(string $key, int $ttl): void
	{
		foreach ($this->tags as $tag)
		{
			$tagKey = $this->keyRaw('tag', $tag, 'keys');
			$setTtl = \max($this->cache->ttl($tagKey), $ttl);

			if ($setTtl < 0)
				$setTtl = null;

			$this->cache->sadd($tagKey, $key);
			$this->cache->expire($tagKey, $setTtl);
		}
	}

	/**
	 * Returns TRUE if the given key exists.
	 *
	 * @param string $key
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function exists(string $key): bool
	{
		$key = $this->key($key);

		return $this->cache->exists($key);
	}

	/**
	 * Removes all keys that match our tags.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function flush(): void
	{
		$remove = [];

		foreach ($this->tags as $tag)
		{
			$tagKey = $this->keyRaw('tag', $tag, 'keys');
			$members = $this->cache->smembers($tagKey);
			$members[] = $tagKey; // Also remove the set as well.

			$remove = \array_merge($remove, $members);
		}

		foreach ($remove as $key)
			$this->cache->del($key);
	}

	/**
	 * Gets the value of the given key.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function get(string $key)
	{
		$key = $this->key($key);

		return $this->cache->get($key);
	}

	/**
	 * Sets the given value to the given key.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param int $ttl
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function set(string $key, $value, int $ttl): bool
	{
		$key = $this->key($key);

		$this->linkTags($key, $ttl);

		return $this->cache->setex($key, $value, $ttl);
	}

}