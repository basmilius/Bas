<?php
declare(strict_types=1);

namespace Bas\Storage\Lexer;

/**
 * Class Type
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Storage\Lexer
 * @since 1.0.0
 */
final class Type
{

	public const BOOLEAN = 5;
	public const COMMENT = 4;
	public const DELIMITER = 9;
	public const INVALID = 0;
	public const KEYWORD = 1;
	public const LABEL = 10;
	public const NUMBER = 6;
	public const OPERATOR = 2;
	public const STRING = 7;
	public const SYMBOL = 8;
	public const WHITESPACE = 3;

}
