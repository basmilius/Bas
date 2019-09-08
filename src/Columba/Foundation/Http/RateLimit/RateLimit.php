<?php
/**
 * Copyright (c) 2019 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Foundation\Http\RateLimit;

use Columba\Foundation\Http\RateLimit\Storage\IStorageAdapter;

/**
 * Class RateLimit
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Foundation\Http\RateLimit
 * @since 1.6.0
 */
abstract class RateLimit
{

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var int
	 */
	protected $period;

	/**
	 * @var int
	 */
	protected $requests;

	/**
	 * @var IStorageAdapter
	 */
	protected $storage;

	/**
	 *
	 */

	/**
	 * RateLimit constructor.
	 *
	 * @param string          $name
	 * @param int             $requests
	 * @param int             $period
	 * @param IStorageAdapter $storageAdapter
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(string $name, int $requests, int $period, IStorageAdapter $storageAdapter)
	{
		$this->name = $name;
		$this->period = $period;
		$this->requests = $requests;
		$this->storage = $storageAdapter;
	}

	/**
	 * Returns TRUE if a request is allowed.
	 *
	 * @param string $id
	 * @param int    $use
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public abstract function isAllowed(string $id, int $use = 1): bool;

	/**
	 * Gets the remaining allowance.
	 *
	 * @param string $id
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function getAllowance(string $id): int
	{
		$this->isAllowed($id, 0);
		$key = $this->keyAllowance($id);

		if (!$this->storage->exists($key))
			return $this->requests;

		return (int)max(0, floor($this->storage->get($key)));
	}

	/**
	 * Gets the name of the rate limiter.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function getName(): string
	{
		return $this->name;
	}

	/**
	 * Gets the rate limiting period.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function getPeriod(): int
	{
		return $this->period;
	}

	/**
	 * Gets the max amount of requests.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function getRequests(): int
	{
		return $this->requests;
	}

	/**
	 * Purges data for an id.
	 *
	 * @param string $id
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function purge(string $id)
	{
		$this->storage->remove($this->keyAllowance($id));
		$this->storage->remove($this->keyTime($id));
	}

	/**
	 * Creates a key for an id.
	 *
	 * @param string $id
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function key(string $id): string
	{
		return $this->name . ':' . $id;
	}

	/**
	 * Creates an allowance key for an id.
	 *
	 * @param string $id
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function keyAllowance(string $id): string
	{
		return $this->key($id) . ':allowance';
	}

	/**
	 * Creates a time key for an id.
	 *
	 * @param string $id
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function keyTime(string $id): string
	{
		return $this->key($id) . ':time';
	}

}
