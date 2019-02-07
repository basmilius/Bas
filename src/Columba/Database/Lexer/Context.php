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

namespace Columba\Database\Lexer;

/**
 * Class Context
 *
 * @package Columba\Database\Lexer
 * @author Bas Milius <bas@mili.us>
 * @since 1.0.0
 */
final class Context
{

	/**
	 * @var int
	 */
	private static $mode;

	/**
	 * Checks if the given {@see $str} is a boolean value.
	 *
	 * @param string $str
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function isBool(string $str): bool
	{
		$str = strtoupper($str);

		return $str === 'TRUE' || $str === 'FALSE';
	}

	/**
	 * Checks if the given {@see $str} is the beginning of a whitespace.
	 *
	 * @param string $str
	 * @param bool   $end
	 *
	 * @return int|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function isComment(string $str, bool $end = false): ?int
	{
		$length = strlen($str);

		if ($length === 0)
			return null;

		if ($str[0] === '#')
			return Flag::COMMENT_BASH;

		if ($length > 1 && $str[0] === '/' && $str[1] === '*')
			return ($length > 2 && $str[2] === '!') ? Flag::COMMENT_MYSQL_CMD : Flag::COMMENT_C;

		if ($length > 1 && $str[0] === '*' && $str[1] === '/')
			return Flag::COMMENT_C;

		if ($length > 2 && $str[0] === '-' && $str[1] === '-' && self::isWhitespace($str[2]))
			return Flag::COMMENT_SQL;

		if ($length === 2 && $end && $str[0] === '-' && $str[1] === '-')
			return Flag::COMMENT_SQL;

		return null;
	}

	/**
	 * Checks if the given {@see $str} is a keywords.
	 *
	 * @param string $str
	 * @param bool   $isReserved
	 *
	 * @return int|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function isKeyword(string $str, bool $isReserved = false): ?int
	{
		$str = strtoupper($str);

		if (isset(Consts::KEYWORDS[$str]))
		{
			if ($isReserved && !(Consts::KEYWORDS[$str] & Flag::KEYWORD_RESERVED))
				return null;

			return Consts::KEYWORDS[$str];
		}

		return null;
	}

	/**
	 * Checks if the given {@see $str} is part of a number.
	 *
	 * @param string $str
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function isNumber(string $str): bool
	{
		return ($str >= '0' && $str <= '9') || $str === '.' || $str === '-' || $str === '+' || $str === 'e' || $str === 'E';
	}

	/**
	 * Checks if the given {@see $str} is an operator.
	 *
	 * @param string $str
	 *
	 * @return int|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function isOperator(string $str): ?int
	{
		if (isset(Consts::OPERATORS[$str]))
			return Consts::OPERATORS[$str];

		return null;
	}

	/**
	 * Checks if the given {@see $str} is a separator of two lexeme.
	 *
	 * @param string $str
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function isSeparator(string $str): bool
	{
		return $str <= '~' && $str !== '_' && ($str < '0' || $str > '9') && ($str < 'a' || $str > 'z') && ($str < 'A' || $str > 'Z');
	}

	/**
	 * Checks if the given {@see $str} is the beginning of a string.
	 *
	 * @param string $str
	 *
	 * @return int|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function isString(string $str): ?int
	{
		if (strlen($str) === 0)
			return null;

		if ($str[0] === '\'')
			return Flag::STRING_SINGLE_QUOTES;

		if ($str[0] === '"')
			return Flag::STRING_DOUBLE_QUOTES;

		return null;
	}

	/**
	 * Checks if the given {@see $str} is the beginning of a symbol. Can either be a variable or field name.
	 *
	 * @param string $str
	 *
	 * @return int|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function isSymbol(string $str): ?int
	{
		if (strlen($str) === 0)
			return null;

		if ($str[0] === '@')
			return Flag::SYMBOL_VARIABLE;

		if ($str[0] === '`')
			return Flag::SYMBOL_BACKTICK;

		if ($str[0] === ':')
			return Flag::SYMBOL_PARAMETER;

		return null;
	}

	/**
	 * Checks if the given character is a whitespace.
	 *
	 * @param string $str
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function isWhitespace(string $str): bool
	{
		return $str === ' ' || $str === "\r" || $str === "\n" || $str === "\t";
	}

	/**
	 * Escapes the symvol by adding surrounding backticks.
	 *
	 * @param string|string[] $str
	 * @param string          $quote
	 *
	 * @return array|string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function escape($str, string $quote = '`')
	{
		if (is_array($str))
		{
			foreach ($str as $key => $value)
				$str[$key] = self::escape($value, $quote);

			return $str;
		}

		if (self::$mode & Consts::SQL_MODE_NO_ENCLOSING_QUOTES && !self::isKeyword($str, true))
			return $str;

		if (self::$mode & Consts::SQL_MODE_ANSI_QUOTES)
			$quote = '"';

		return $quote . str_replace($quote, $quote . $quote, $str) . $quote;
	}

	/**
	 * Sets the SQL mode.
	 *
	 * @param string $mode
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function setMode(string $mode): void
	{
		self::$mode = 0;

		if (empty($mode))
			return;

		$mode = explode(',', $mode);

		foreach ($mode as $m)
			self::$mode |= constant('Consts::SQL_MODE_' . $m);
	}

}
