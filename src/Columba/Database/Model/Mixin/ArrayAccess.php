<?php
/**
 * Copyright (c) 2019 - 2020 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Database\Model\Mixin;

/**
 * Trait ArrayAccess
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Model\Mixin
 * @since 1.6.0
 */
trait ArrayAccess
{

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function offsetExists($field): bool
	{
		return $this->hasValue($field);
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function offsetGet($field)
	{
		return $this->getValue($field);
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function offsetSet($field, $value): void
	{
		$this->setValue($field, $value);
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function offsetUnset($field): void
	{
		$this->unsetValue($field);
	}

}
