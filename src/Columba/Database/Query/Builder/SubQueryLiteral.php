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
 * Class SubQueryLiteral
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Query\Builder
 * @since 1.6.0
 */
class SubQueryLiteral extends ComparatorAwareLiteral implements IAfterPiece
{

	protected string $clause;
	protected Base $query;

	/**
	 * SubQueryLiteral constructor.
	 *
	 * @param Base $query
	 * @param string $clause
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(Base $query, string $clause = '')
	{
		parent::__construct('');

		$this->clause = $clause;
		$this->query = $query;
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function after(Base $query): void
	{
		if ($this->clause !== '')
			$query->addPiece($this->clause, '');

		$query->parenthesis(fn(Base $query) => $query->merge($this->query, 1), false);
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function value(Base $query): string
	{
		return '';
	}

}
