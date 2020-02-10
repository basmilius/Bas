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
 * Class Literal
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Query\Builder
 * @since 1.6.0
 */
class Literal extends Value
{

	protected string $literal;

	/**
	 * Literal constructor.
	 *
	 * @param string $literal
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(string $literal)
	{
		$this->literal = $literal;
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function value(Base $query): string
	{
		return $this->literal;
	}

}
