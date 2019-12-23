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

namespace Columba\Database\Query\Builder;

/**
 * Returns a "x ASC" {@see Literal} instance.
 *
 * @param string $column
 *
 * @return Literal
 * @author Bas Milius <bas@mili.us>
 * @since 1.6.0
 */
function asc(string $column): Literal
{
	return new Literal("$column ASC");
}

/**
 * Returns a "x DESC" {@see Literal} instance.
 *
 * @param string $column
 *
 * @return Literal
 * @author Bas Milius <bas@mili.us>
 * @since 1.6.0
 */
function desc(string $column): Literal
{
	return new Literal("$column DESC");
}

/**
 * Returns a "BETWEEN x AND y" {@see Literal} instance.
 *
 * @param int $from
 * @param int $to
 *
 * @return Literal
 * @author Bas Milius <bas@mili.us>
 * @since 1.6.0
 */
function between(int $from, int $to): Literal
{
	return new ComparatorAwareLiteral("BETWEEN $from AND $to");
}

/**
 * Returns an "IS NOT NULL" {@see Literal} instance.
 *
 * @return Literal
 * @author Bas Milius <bas@mili.us>
 * @since 1.6.0
 */
function isNotNull(): Literal
{
	return new ComparatorAwareLiteral('IS NOT NULL');
}

/**
 * Returns an "IS NULL" {@see Literal} instance.
 *
 * @return Literal
 * @author Bas Milius <bas@mili.us>
 * @since 1.6.0
 */
function isNull(): Literal
{
	return new ComparatorAwareLiteral('IS NULL');
}

/**
 * Returns a {@see Literal} instance.
 *
 * @param string|int|bool $literal
 *
 * @return Literal
 * @author Bas Milius <bas@mili.us>
 * @since 1.6.0
 */
function literal($literal): Literal
{
	if (is_bool($literal))
		$literal = $literal ? 1 : 0;

	return new Literal(strval($literal));
}

/**
 * Returns a string {@see Literal} instance.
 *
 * @param string $literal
 *
 * @return Literal
 * @author Bas Milius <bas@mili.us>
 * @since 1.0.0
 */
function stringLiteral(string $literal): Literal
{
	$literal = addslashes($literal);

	return new Literal("'$literal'");
}

/**
 * Returns an "UNIX_TIMESTAMP()" {@see Literal} instance.
 *
 * @return Literal
 * @author Bas Milius <bas@mili.us>
 * @since 1.6.0
 */
function unixTimestamp(): Literal
{
	return new Literal('UNIX_TIMESTAMP()');
}
