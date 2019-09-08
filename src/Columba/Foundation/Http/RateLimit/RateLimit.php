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

	protected $name;
	protected $period;
	protected $requests;
	protected $storage;

	public function __construct(string $name, int $requests, int $period, IStorageAdapter $storageAdapter)
	{
		$this->name = $name;
		$this->period = $period;
		$this->requests = $requests;
		$this->storage = $storageAdapter;
	}

	public abstract function isAllowed(string $id, int $use = 1): bool;

	public final function getAllowance(string $id): int
	{
		$this->isAllowed($id, 0);
		$key = $this->keyAllowance($id);

		if (!$this->storage->exists($key))
			return $this->requests;

		return (int)max(0, floor($this->storage->get($key)));
	}

	public final function getName(): string
	{
		return $this->name;
	}

	public final function getPeriod(): int
	{
		return $this->period;
	}

	public final function getRequests(): int
	{
		return $this->requests;
	}

	public final function purge(string $id)
	{
		$this->storage->remove($this->keyAllowance($id));
		$this->storage->remove($this->keyTime($id));
	}

	protected function key(string $id): string
	{
		return $this->name . ':' . $id;
	}

	protected function keyAllowance(string $id): string
	{
		return $this->key($id) . ':allowance';
	}

	protected function keyTime(string $id): string
	{
		return $this->key($id) . ':time';
	}

}
