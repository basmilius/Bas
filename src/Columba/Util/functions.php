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

namespace Columba\Util;

use Columba\Foundation\System;
use function count;
use function headers_list;

/**
 * Calls {@see $fn} and wraps it with <pre> tags, or not if we're in cli.
 *
 * @param callable $fn
 * @param mixed    ...$data
 *
 * @author Bas Milius <bas@mili.us>
 * @since 1.5.0
 */
function _pre(callable $fn, ...$data)
{
	$shouldEcho = !System::isCLI() && !in_array('Content-type: text/plain;charset=UTF-8', headers_list());

	if (count($data) === 1 && ArrayUtil::isSequentialArray($data))
		$data = $data[0];

	if ($shouldEcho)
		echo '<pre>';

	$fn($data);
	echo PHP_EOL;

	if ($shouldEcho)
		echo '</pre>';
}

/**
 * var_dump.
 *
 * @param mixed ...$data
 *
 * @author Bas Milius <bas@mili.us>
 * @since 1.5.0
 */
function dump(...$data): void
{
	_pre('var_dump', ...$data);
}

/**
 * var_dump and die.
 *
 * @param mixed ...$data
 *
 * @author Bas Milius <bas@mili.us>
 * @since 1.5.0
 */
function dumpDie(...$data): void
{
	_pre('var_dump', ...$data);
	die;
}

/**
 * print_r.
 *
 * @param mixed ...$data
 *
 * @author Bas Milius <bas@mili.us>
 * @since 1.5.0
 */
function pre(...$data): void
{
	_pre('print_r', ...$data);
}

/**
 * print_r and die.
 *
 * @param mixed ...$data
 *
 * @author Bas Milius <bas@mili.us>
 * @since 1.5.0
 */
function preDie(...$data): void
{
	_pre('print_r', ...$data);
	die;
}
