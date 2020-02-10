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
 * Interface IBeforePiece
 *
 * @package Columba\Database\Query\Builder
 *
 * @author Bas Milius <bas@mili.us>
 * @since 1.6.0
 */
interface IBeforePiece
{

	/**
	 * Executed before a piece is added to the given query.
	 *
	 * @param Base $query
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function before(Base $query): void;

}
