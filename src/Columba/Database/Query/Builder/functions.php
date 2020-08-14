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

use Columba\Database\Db;

/**
 * Returns an "EXISTS $query" {@see Literal} instance.
 *
 * @param Builder $query
 *
 * @return Literal
 * @author Bas Milius <bas@mili.us>
 * @since 1.6.0
 */
function exists(Builder $query): Literal
{
	return new SubQueryLiteral($query, 'EXISTS');
}

/**
 * Returns an "NOT EXISTS $query" {@see Literal} instance.
 *
 * @param Builder $query
 *
 * @return Literal
 * @author Bas Milius <bas@mili.us>
 * @since 1.6.0
 */
function notExists(Builder $query): Literal
{
	return new SubQueryLiteral($query, 'NOT EXISTS');
}

/**
 * Returns a "$query" {@see Literal} instance.
 *
 * @param Builder $query
 *
 * @return Literal
 * @author Bas Milius <bas@mili.us>
 * @since 1.6.0
 */
function subQuery(Builder $query): Literal
{
	return new SubQueryLiteral($query);
}

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
 * Returns a "IN (x)" {@see Literal} instance.
 *
 * @param array $options
 *
 * @return Literal
 * @author Bas Milius <bas@mili.us>
 * @since 1.6.0
 */
function in(array $options): Literal
{
	$options = array_map(fn($option) => is_int($option) ? $option : Db::quote($option), $options);
	$options = implode(',', $options);

	return new ComparatorAwareLiteral("IN ($options)");
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
 * Returns a comparator aware {@see Literal}.
 *
 * @param string $literal
 *
 * @return Literal
 * @author Bas Milius <bas@mili.us>
 * @since 1.6.0
 */
function comparatorLiteral(string $literal): Literal
{
	return new ComparatorAwareLiteral($literal);
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
