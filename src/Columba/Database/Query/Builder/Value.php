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

namespace Columba\Database\Query\Builder;

/**
 * Class Value
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Query\Builder
 * @since 1.6.0
 */
abstract class Value
{

	/**
	 * Gets the value. This method also gives the {@see Base} instance so params can be set.
	 *
	 * @param Base $query
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public abstract function value(Base $query): string;

}
