<?php
/**
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Database\Lexer;

/**
 * Class Flag
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Lexer
 * @since 1.0.0
 */
final class Flag
{

	// Flags that describe tokens in more detail.
	public const KEYWORD_COMPOSED = 4;
	public const KEYWORD_DATA_TYPE = 8;
	public const KEYWORD_KEY = 16;
	public const KEYWORD_FUNCTION = 32;
	public const KEYWORD_RESERVED = 2;

	// Number related flags.
	public const NUMBER_HEX = 1;
	public const NUMBER_FLOAT = 2;
	public const NUMBER_APPROXIMATE = 4;
	public const NUMBER_NEGATIVE = 8;
	public const NUMBER_BINARY = 16;

	// String related flags.
	public const STRING_SINGLE_QUOTES = 1;
	public const STRING_DOUBLE_QUOTES = 2;

	// Comment related flags.
	public const COMMENT_BASH = 1;
	public const COMMENT_C = 2;
	public const COMMENT_SQL = 4;
	public const COMMENT_MYSQL_CMD = 8;

	// Operator related flags.
	public const OPERATOR_ARITHMETIC = 1;
	public const OPERATOR_LOGICAL = 2;
	public const OPERATOR_BITWISE = 4;
	public const OPERATOR_ASSIGNMENT = 8;
	public const OPERATOR_SQL = 16;

	// Symbol related flags.
	public const SYMBOL_VARIABLE = 1;
	public const SYMBOL_BACKTICK = 2;
	public const SYMBOL_USER = 4;
	public const SYMBOL_SYSTEM = 8;
	public const SYMBOL_PARAMETER = 16;

}
