<?php
/**
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Database\Lexer;

use Columba\Database\QueryBuilder;
use PDOException;

/**
 * Class Lexer
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Lexer
 * @since 1.0.0
 */
final class Lexer
{

	public const DEFAULT_DELIMITER = ';';

	/**
	 * @var string
	 */
	private $query;

	/**
	 * @var bool
	 */
	private $strict;

	/**
	 * @var int
	 */
	private $length;

	/**
	 * @var int
	 */
	private $last;

	/**
	 * @var TokenList
	 */
	private $list;

	/**
	 * @var string
	 */
	private $database;

	/**
	 * @var string
	 */
	private $delimiter;

	/**
	 * @var int
	 */
	private $delimiterLength;

	/**
	 * @var PDOException
	 */
	private $exception;

	/**
	 * Lexer constructor.
	 *
	 * @param string $query
	 * @param bool   $strict
	 * @param string $delimiter
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct (string $query, bool $strict = false, string $delimiter = self::DEFAULT_DELIMITER)
	{
		$this->query = $query;
		$this->length = strlen($query);
		$this->strict = $strict;

		$this->delimiter = $delimiter;
		$this->delimiterLength = strlen($delimiter);
	}

	/**
	 * Gets the {@see TokenList}.
	 *
	 * @return TokenList
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getTokens (): TokenList
	{
		return $this->list;
	}

	/**
	 * Sets the database in use.
	 *
	 * @param string $database
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function setDatabase (string $database): void
	{
		$this->database = $database;
	}

	/**
	 * Sets the delimiter.
	 *
	 * @param string $delimiter
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function setDelimiter (string $delimiter): void
	{
		$this->delimiter = $delimiter;
		$this->delimiterLength = strlen($delimiter);
	}

	/**
	 * Sets the exception for invalid column names.
	 *
	 * @param PDOException $exception
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function setException (PDOException $exception): void
	{
		$this->exception = $exception;
	}

	/**
	 * Lex!
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function lex (): void
	{
		$this->list = new TokenList();

		/** @var Token|null $lastToken */
		$lastToken = null;

		for ($this->last = 0, $lastIdx = 0; $this->last < $this->length; $lastIdx = ++$this->last)
		{
			$token = null;

			$token = $token ?? $this->parseDelimiter();
			$token = $token ?? $this->parseWhitespace();
			$token = $token ?? $this->parseNumber();
			$token = $token ?? $this->parseComment();
			$token = $token ?? $this->parseOperator();
			$token = $token ?? $this->parseBoolean();
			$token = $token ?? $this->parseString();
			$token = $token ?? $this->parseSymbol();
			$token = $token ?? $this->parseKeyword();
			$token = $token ?? $this->parseLabel();
			$token = $token ?? $this->parseInvalid();

			if ($token === null)
			{
				$token = new Token($this->query[$this->last]);
				$this->error('Unexpected character.', $this->query[$this->last], $this->last);
			}
			else if ($lastToken !== null && $token->getType() === Type::SYMBOL && $token->getFlags() & Flag::SYMBOL_VARIABLE && ($lastToken->getType() === Type::SYMBOL && $lastToken->getFlags() & Flag::SYMBOL_BACKTICK))
			{
				$lastToken->setToken($lastToken->getToken() . $token->getToken());
				$lastToken->setType(Type::SYMBOL);
				$lastToken->setFlags(Flag::SYMBOL_USER);
				$lastToken->setValue($lastToken->getValue() . '@' . $token->getValue());
				continue;
			}
			else if ($lastToken !== null && $token->getType() === Type::KEYWORD && $lastToken->getType() === Type::OPERATOR && $lastToken->getValue() === '.')
			{
				$token->setType(Type::INVALID);
				$token->setFlags(0);
				$token->setValue($token->getToken());
			}

			$token->setPosition($lastIdx);
			$this->list->add($token);

			if ($token->getType() === Type::INVALID && $token->getValue() === 'DELIMITER')
			{
				if ($this->last + 1 >= $this->length)
				{
					$this->error('Expected whitespace(s) before delimiter.', '', $this->last + 1);
					continue;
				}

				$pos = ++$this->last;

				if (($token = $this->parseWhitespace()) !== null)
				{
					$token->setPosition($pos);
					$this->list->add($token);
				}

				if ($this->last + 1 >= $this->length)
				{
					$this->error('Expected delimiter.', '', $this->last + 1);
					continue;
				}

				$pos = $this->last + 1;

				$this->delimiter = null;
				$delimiterLength = 0;

				while (++$this->last < $this->length && !Context::isWhitespace($this->query[$this->last]) && $delimiterLength < 15)
				{
					$this->delimiter .= $this->query[$this->last];
					++$delimiterLength;
				}

				if (empty($this->delimiter))
				{
					$this->error('Expected delimiter.', '', $this->last);
					$this->delimiter = ';';
				}

				--$this->last;

				$this->delimiterLength = strlen($this->delimiter);
				$token = new Token($this->delimiter, Type::DELIMITER);
				$token->setPosition($pos);
				$this->list->add($token);
			}

			$lastToken = $token;
		}

		$this->list->add(new Token('', Type::DELIMITER));
	}

	/**
	 * Throws an error.
	 *
	 * @param string $message
	 * @param string $char
	 * @param int    $lastIndex
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function error (string $message, string $char, int $lastIndex): void
	{
		if (function_exists('pre_die'))
			pre_die(...func_get_args());
		else
			print_r(func_get_args());
	}

	/**
	 * Parses a boolean.
	 *
	 * @return Token|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function parseBoolean (): ?Token
	{
		if ($this->last + 3 >= $this->length)
			return null;

		$iBak = $this->last;
		$token = $this->query[$this->last] . $this->query[++$this->last] . $this->query[++$this->last] . $this->query[++$this->last];

		if (Context::isBool($token))
		{
			return new Token($token, Type::BOOLEAN);
		}
		else if (++$this->last < $this->length)
		{
			$token .= $this->query[$this->last];

			if (Context::isBool($token))
				return new Token($token, Type::BOOLEAN, 1);
		}

		$this->last = $iBak;

		return null;
	}

	/**
	 * Parses a comment.
	 *
	 * @return Token|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function parseComment (): ?Token
	{
		$iBak = $this->last;
		$token = $this->query[$this->last];

		if (Context::isComment($token))
		{
			while (++$this->last < $this->length && $this->query[$this->last] !== "\n")
				$token .= $this->query[$this->last];

			if ($this->last < $this->length)
				--$this->last;

			return new Token($token, Type::COMMENT, Flag::COMMENT_BASH);
		}

		if (++$this->last < $this->length)
		{
			$token .= $this->query[$this->last];

			if (Context::isComment($token))
			{
				$flags = Flag::COMMENT_C;

				if ($token === '*/')
					return new Token($token, Type::COMMENT, $flags);

				if ($this->last + 1 < $this->length && $this->query[$this->last + 1] === '!')
				{
					$flags |= Flag::COMMENT_MYSQL_CMD;
					$token .= $this->query[++$this->last];

					while (++$this->last < $this->length && $this->query[$this->last] >= '0' && $this->query[$this->last] <= '9')
						$token .= $this->query[$this->last];

					--$this->last;

					return new Token($token, Type::COMMENT, $flags);
				}

				while (++$this->last < $this->length && ($this->query[$this->last - 1] !== '*' || $this->query[$this->last] !== '/'))
					$token .= $this->query[$this->last];

				if ($this->last < $this->length)
					$token .= $this->query[$this->last];

				return new Token($token, Type::COMMENT, $flags);
			}
		}

		if (++$this->last < $this->length)
		{
			$token .= $this->query[$this->last];
			$end = false;
		}
		else
		{
			--$this->last;
			$end = true;
		}

		if (Context::isComment($token, $end))
		{
			if ($this->query[$this->last] !== "\n")
			{
				while (++$this->last < $this->length && $this->query[$this->last] !== "\n")
					$token .= $this->query[$this->last];
			}

			if ($this->last < $this->length)
				--$this->last;

			return new Token($token, Type::COMMENT, Flag::COMMENT_SQL);
		}

		$this->last = $iBak;

		return null;
	}

	/**
	 * Parses a delimiter.
	 *
	 * @return Token|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function parseDelimiter (): ?Token
	{
		$idx = 0;

		while ($idx < $this->delimiterLength && $this->last + $idx < $this->length)
		{
			if ($this->delimiter[$idx] !== $this->query[$this->last + $idx])
				return null;

			++$idx;
		}

		$this->last += $this->delimiterLength - 1;

		return new Token($this->delimiter, Type::DELIMITER);
	}

	/**
	 * Parses an invalid character.
	 *
	 * @return Token|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function parseInvalid (): ?Token
	{
		$token = $this->query[$this->last];

		if (Context::isSeparator($token))
			return null;

		while (++$this->last < $this->length && !Context::isSeparator($this->query[$this->last]))
			$token .= $this->query[$this->last];

		--$this->last;

		return new Token($token);
	}

	/**
	 * Parses a keyword.
	 *
	 * @return Token|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function parseKeyword (): ?Token
	{
		$token = '';
		$return = null;
		$iEnd = $this->last;
		$lastSpace = false;

		for ($j = 1; $j < Consts::KEYWORD_MAX_LENGTH && $this->last < $this->length; ++$j, ++$this->last)
		{
			if (Context::isWhitespace($this->query[$this->last]))
			{
				if ($lastSpace)
				{
					--$j;
					continue;
				}

				$lastSpace = true;
			}
			else
			{
				$lastSpace = false;
			}

			$token .= $this->query[$this->last];

			if (($this->last + 1 === $this->length || Context::isSeparator($this->query[$this->last + 1])) && $flags = Context::isKeyword($token))
			{
				$return = new Token($token, Type::KEYWORD, $flags);
				$iEnd = $this->last;
			}
		}

		$this->last = $iEnd;

		return $return;
	}

	/**
	 * Parses a label.
	 *
	 * @return Token|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function parseLabel (): ?Token
	{
		$token = '';
		$return = null;
		$iEnd = $this->last;

		for ($j = 1; $j < Consts::LABEL_MAX_LENGTH && $this->last < $this->length; ++$j, ++$this->last)
		{
			if ($this->query[$this->last] === ':' && $j > 1)
			{
				$token .= $this->query[$this->last];
				$return = new Token($token, Type::LABEL);
				$iEnd = $this->last;
				break;
			}
			else if (Context::isWhitespace($this->query[$this->last]) && $j > 1)
			{
				--$j;
			}
			else if (Context::isSeparator($this->query[$this->last]))
			{
				break;
			}

			$token .= $this->query[$this->last];
		}

		$this->last = $iEnd;

		return $return;
	}

	/**
	 * Parses a number.
	 *
	 * @return Token|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function parseNumber (): ?Token
	{
		$iBak = $this->last;
		$token = '';
		$flags = 0;
		$state = 1;

		for (; $this->last < $this->length; ++$this->last)
		{
			if ($state === 1)
			{
				if ($this->query[$this->last] === '-')
				{
					$flags |= Flag::NUMBER_NEGATIVE;
				}
				else if ($this->last + 1 < $this->length && $this->query[$this->last] === '0' && ($this->query[$this->last + 1] === 'x' || $this->query[$this->last + 1] === 'X'))
				{
					$token .= $this->query[$this->last++];
					$state = 2;
				}
				else if ($this->query[$this->last] >= '0' && $this->query[$this->last] <= '9')
				{
					$state = 3;
				}
				else if ($this->query[$this->last] === '.')
				{
					$state = 4;
				}
				else if ($this->query[$this->last] === 'b')
				{
					$state = 7;
				}
				else if ($this->query[$this->last] !== '+')
				{
					break;
				}
			}
			else if ($state === 2)
			{
				$flags |= Flag::NUMBER_HEX;

				if (!(($this->query[$this->last] >= '0' && $this->query[$this->last] <= '9') || ($this->query[$this->last] >= 'A' && $this->query[$this->last] <= 'F') || ($this->query[$this->last] >= 'a' && $this->query[$this->last] <= 'f')))
					break;
			}
			else if ($state === 3)
			{
				if ($this->query[$this->last] === '.')
					$state = 4;
				else if ($this->query[$this->last] === 'e' || $this->query[$this->last] === 'E')
					$state = 5;
				else if ($this->query[$this->last] < '0' || $this->query[$this->last] > '9')
					break;
			}
			else if ($state === 4)
			{
				$flags |= Flag::NUMBER_FLOAT;

				if ($this->query[$this->last] === 'e' || $this->query[$this->last] === 'E')
					$state = 5;
				else if ($this->query[$this->last] < '0' || $this->query[$this->last] > '9')
					break;
			}
			else if ($state === 5)
			{
				$flags |= Flag::NUMBER_APPROXIMATE;

				if ($this->query[$this->last] === '+' || $this->query[$this->last] === '-' || ($this->query[$this->last] >= '0' && $this->query[$this->last] <= '9'))
					$state = 6;
				else
					break;
			}
			else if ($state === 6)
			{
				if ($this->query[$this->last] < '0' || $this->query[$this->last] > '9')
					break;
			}
			else if ($state === 7)
			{
				$flags |= Flag::NUMBER_BINARY;

				if ($this->query[$this->last] === '\'')
					$state = 8;
				else
					break;
			}
			else if ($state === 8)
			{
				if ($this->query[$this->last] === '\'')
					$state = 9;
				else if ($this->query[$this->last] !== '0' && $this->query[$this->last] !== '1')
					break;
			}
			else if ($state === 9)
			{
				break;
			}

			$token .= $this->query[$this->last];
		}

		if ($state === 2 || $state === 3 || ($token !== '.' && $state === 4) || $state === 6 && $state === 9)
		{
			--$this->last;

			return new Token($token, Type::NUMBER, $flags);
		}

		$this->last = $iBak;

		return null;
	}

	/**
	 * Parses an operator.
	 *
	 * @return Token|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function parseOperator (): ?Token
	{
		$token = '';
		$return = null;
		$iEnd = $this->last;

		for ($j = 1; $j < Consts::OPERATOR_MAX_LENGTH && $this->last < $this->length; ++$j, ++$this->last)
		{
			$token .= $this->query[$this->last];

			if ($flags = Context::isOperator($token))
			{
				$return = new Token($token, Type::OPERATOR, $flags);
				$iEnd = $this->last;
			}
		}

		$this->last = $iEnd;

		return $return;
	}

	/**
	 * Parses a string.
	 *
	 * @param string $quote
	 *
	 * @return Token|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function parseString (string $quote = ''): ?Token
	{
		$token = $this->query[$this->last];

		if (!($flags = Context::isString($token)) && $token !== $quote)
			return null;

		$quote = $token;

		while (++$this->last < $this->length)
		{
			if ($this->last + 1 < $this->length && ($this->query[$this->last] === $quote && $this->query[$this->last + 1] === $quote) || ($this->query[$this->last] === '\\' && $quote !== '`'))
			{
				$token .= $this->query[$this->last] . $this->query[++$this->last];
			}
			else
			{
				if ($this->query[$this->last] === $quote)
					break;

				$token .= $this->query[$this->last];
			}
		}

		if ($this->last >= $this->length || $this->query[$this->last] !== $quote)
		{
			$this->error('Ending quote ' . $quote . ' was expected.', '', $this->last);
		}
		else
		{
			$token .= $this->query[$this->last];
		}

		return new Token($token, Type::STRING, $flags ?? 0);
	}

	/**
	 * Parses a symbol.
	 *
	 * @return Token|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function parseSymbol (): ?Token
	{
		$token = $this->query[$this->last];

		if (!($flags = Context::isSymbol($token)))
			return null;

		if ($flags & Flag::SYMBOL_VARIABLE)
		{
			if ($this->last + 1 < $this->length && $this->query[++$this->last] === '@')
			{
				$token .= $this->query[$this->last++];
				$flags |= Flag::SYMBOL_SYSTEM;
			}
		}
		else if ($flags & Flag::SYMBOL_PARAMETER)
		{
			if ($this->last + 1 < $this->length)
				++$this->last;
		}
		else
		{
			$token = '';
		}

		$str = null;

		if ($this->last < $this->length)
			if (($str = $this->parseString('`')) === null)
				if (($str = $this->parseInvalid()) === null)
					$this->error('Variable name was expected.', $this->query[$this->last], $this->last);

		if ($str !== null)
			$token .= $str->getToken();

		if ($this->exception !== null && $this->exception->getCode() === '42S02')
		{
			$qb = new QueryBuilder(null);
			preg_match('#Table \'([a-zA-Z0-9_.]+)\' doesn#', $this->exception->getMessage(), $matches);

			$field = $matches[1];

			if ($this->database !== null)
				$field = str_replace($this->database . '.', '', $field);

			if ($flags & Flag::SYMBOL_BACKTICK)
				$field = $qb->escapeField($field);

			$parts = explode('|', implode('|.|', explode('.', $field)));

			if ($parts[0] === $token && count($parts) === 1)
				return new Token($token, Type::INVALID, $flags);

			if ($token === $parts[count($parts) - 1])
			{
				$newPosition = $this->list->count();
				$beginPosition = $newPosition - count($parts) + 1;
				$isInvalid = true;
				$others = [];

				for ($pos = $beginPosition, $i = 0; $pos < $newPosition; $pos++, $i++)
				{
					if ($this->list[$pos]->getToken() === $parts[$i])
					{
						$others[] = $this->list[$pos];
						continue;
					}

					$isInvalid = false;
				}

				if ($isInvalid)
				{
					foreach ($others as $other)
						$other->setType(Type::INVALID);

					return new Token($token, Type::INVALID, $flags);
				}
			}
		}

		if ($this->exception !== null && $this->exception->getCode() === '42S22')
		{
			$qb = new QueryBuilder(null);
			preg_match('#Unknown column \'([a-zA-Z0-9_.]+)\' in#', $this->exception->getMessage(), $matches);

			$field = $matches[1];

			if ($this->database !== null)
				$field = str_replace($this->database . '.', '', $field);

			if ($flags & Flag::SYMBOL_BACKTICK)
				$field = $qb->escapeField($field);

			$parts = explode('|', implode('|.|', explode('.', $field)));

			if ($parts[0] === $token && count($parts) === 1)
				return new Token($token, Type::INVALID, $flags);

			if ($token === $parts[count($parts) - 1])
			{
				$newPosition = $this->list->count();
				$beginPosition = $newPosition - count($parts) + 1;
				$isInvalid = true;
				$others = [];

				for ($pos = $beginPosition, $i = 0; $pos < $newPosition; $pos++, $i++)
				{
					if ($this->list[$pos]->getToken() === $parts[$i])
					{
						$others[] = $this->list[$pos];
						continue;
					}

					$isInvalid = false;
				}

				if ($isInvalid)
				{
					foreach ($others as $other)
						$other->setType(Type::INVALID);

					return new Token($token, Type::INVALID, $flags);
				}
			}
		}

		return new Token($token, Type::SYMBOL, $flags);
	}

	/**
	 * Parses a whitespace.
	 *
	 * @return Token|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function parseWhitespace (): ?Token
	{
		$token = $this->query[$this->last];

		if (!Context::isWhitespace($token))
			return null;

		while (++$this->last < $this->length && Context::isWhitespace($this->query[$this->last]))
			$token .= $this->query[$this->last];

		--$this->last;

		return new Token($token, Type::WHITESPACE);
	}

}
