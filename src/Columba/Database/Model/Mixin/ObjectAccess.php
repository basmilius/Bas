<?php
/**
 * Copyright (c) 2017 - 2019 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Latte Framework package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Database\Model\Mixin;

/**
 * Trait ObjectAccess
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Model\Mixin
 * @since 1.6.0
 */
trait ObjectAccess
{

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __get(string $column)
	{
		return $this->getValue($column);
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __isset(string $column): bool
	{
		return $this->hasValue($column);
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __set(string $column, $value): void
	{
		$this->setValue($column, $value);
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __unset(string $column): void
	{
		$this->unsetValue($column);
	}

}
