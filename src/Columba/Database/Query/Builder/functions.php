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
 * Returns an "IS NULL" {@see Literal} instance.
 *
 * @return Literal
 * @author Bas Milius <bas@mili.us>
 * @since 1.6.0
 */
function isNull(): Literal
{
	return new Literal('IS NULL');
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
