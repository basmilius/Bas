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

/**
 * Class TimeWindowRateLimit
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Foundation\Http\RateLimit
 * @since 1.6.0
 */
class TimeWindowRateLimit extends RateLimit
{

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function isAllowed(string $id, int $use = 1): bool
	{
		$currentTime = time();

		$allowanceKey = $this->keyAllowance($id);
		$timeKey = $this->keyTime($id);

		if (!$this->storage->exists($timeKey))
		{
			$this->storage->set($allowanceKey, ($this->requests - $use), $this->period);
			$this->storage->set($timeKey, $currentTime, $this->period);

			return true;
		}

		$allowance = $this->storage->get($allowanceKey);

		if ($allowance < $use)
			return false;

		$this->storage->set($allowanceKey, $allowance - $use, $this->period);

		return true;
	}

}
