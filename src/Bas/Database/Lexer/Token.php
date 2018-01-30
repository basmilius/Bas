<?php
/**
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Bas package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bas\Database\Lexer;

use ReflectionClass;

/**
 * Class Token
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Database\Lexer
 * @since 1.0.0
 */
final class Token
{

	/**
	 * @var string
	 */
	private $token;

	/**
	 * @var mixed
	 */
	private $value;

	/**
	 * @var null
	 */
	private $keyword;

	/**
	 * @var int
	 */
	private $type;

	/**
	 * @var int
	 */
	private $flags;

	/**
	 * @var int
	 */
	private $position;

	/**
	 * Token constructor.
	 *
	 * @param string $token
	 * @param int    $type
	 * @param int    $flags
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct (string $token, int $type = Type::INVALID, int $flags = 0)
	{
		$this->token = $token;
		$this->type = $type;
		$this->flags = $flags;
		$this->keyword = null;
		$this->value = $this->extract();
	}

	/**
	 * Gets the token flags.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getFlags (): int
	{
		return $this->flags;
	}

	/**
	 * Gets the HTML string.
	 *
	 * @return string
	 * @throws \ReflectionException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getHtml (): string
	{
		if ($this->type === Type::WHITESPACE)
		{
			$whitespaces = [' ', "\r", "\n", "\t"];
			$replacements = [
				'<span class="syntax-sql type-whitespace whitespace-space"></span>',
				'<span class="syntax-sql type-whitespace whitespace-line-break"></span>',
				'<span class="syntax-sql type-whitespace whitespace-line-break"></span>',
				'<span class="syntax-sql type-whitespace whitespace-tab"></span>'
			];

			return str_replace($whitespaces, $replacements, $this->token);
		}

		return '<span class="syntax-sql type-' . strtolower($this->getTypeName()) . '">' . htmlspecialchars($this->getToken()) . '</span>';
	}

	/**
	 * Gets the token position.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getPosition (): int
	{
		return $this->position;
	}

	/**
	 * Gets the token.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getToken (): string
	{
		return $this->token;
	}

	/**
	 * Gets the token type.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getType (): int
	{
		return $this->type;
	}

	/**
	 * Gets the type name.
	 *
	 * @return string|null
	 * @throws \ReflectionException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getTypeName (): ?string
	{
		$class = new ReflectionClass(Type::class);
		$constants = $class->getConstants();
		$constants = array_flip($constants);

		return $constants[$this->type] ?? null;
	}

	/**
	 * Gets the value.
	 *
	 * @return bool|int|mixed|string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getValue ()
	{
		return $this->value;
	}

	/**
	 * Converts the token into an inline token by replacing tabs and new lines.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getInlineToken (): string
	{
		return str_replace(
			["\r", "\n", "\t"],
			['\r', '\n', '\t'],
			$this->token
		);
	}

	/**
	 * Sets the flags.
	 *
	 * @param int $flags
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function setFlags (int $flags): void
	{
		$this->flags = $flags;
	}

	/**
	 * Sets the position.
	 *
	 * @param int $position
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function setPosition (int $position): void
	{
		$this->position = $position;
	}

	/**
	 * Sets the token.
	 *
	 * @param string $token
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function setToken (string $token): void
	{
		$this->token = $token;
	}

	/**
	 * Sets the type.
	 *
	 * @param int $type
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function setType (int $type): void
	{
		$this->type = $type;
	}

	/**
	 * Sets the value.
	 *
	 * @param string $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function setValue (string $value): void
	{
		$this->value = $value;
	}

	/**
	 * Does little processing to the token to extract a value.
	 *
	 * @return bool|int|mixed|string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function extract ()
	{
		switch ($this->type)
		{
			case Type::BOOLEAN:
				return strtoupper($this->token) === 'TRUE';

			case Type::KEYWORD:
				if (!($this->flags & Flag::KEYWORD_RESERVED))
					return $this->token;

				return $this->keyword = strtoupper($this->token);

			case Type::NUMBER:
				$return = str_replace('--', '', $this->token);

				if ($this->flags & Flag::NUMBER_HEX)
				{
					if ($this->flags & Flag::NUMBER_NEGATIVE)
					{
						$return = str_replace('-', '', $this->token);
						sscanf($return, '%x', $return);
						$return = -$return;
					}
					else
					{
						sscanf($return, '%x', $return);
					}
				}
				else if (($this->flags & Flag::NUMBER_APPROXIMATE) || ($this->flags & Flag::NUMBER_FLOAT))
				{
					sscanf($return, '%f', $return);
				}
				else
				{
					sscanf($return, '%d', $return);
				}

				return $return;

			case Type::STRING:
				$str = $this->token;
				$str = mb_substr($str, 1, -1, 'UTF-8');
				$quote = $this->token[0];
				$str = str_replace($quote . $quote, $quote, $str);

				$str = str_replace('\f', 'f', $str);
				$str = str_replace('\v', 'v', $str);
				$str = stripcslashes($str);

				return $str;

			case Type::SYMBOL:
				$str = $this->token;
				if (isset($str[0]) && $str[0] === '@')
					$str = mb_substr($str, (!empty($str[1]) && $str[1] === '@' ? 2 : 1), mb_strlen($str), 'UTF-8');

				if (isset($str[0]) && $str[0] === ':')
					$str = mb_substr($str, 1, mb_strlen($str), 'UTF-8');

				if (isset($str[0]) && ($str[0] === '`' || $str[0] === '"' || $str === '\''))
				{
					$quote = $str[0];
					$str = str_replace($quote . $quote, $quote, $str);
					$str = mb_substr($str, 1, -1, 'UTF-8');
				}

				return $str;

			case TYPE::WHITESPACE:
				return ' ';
		}

		return $this->token;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function __debugInfo ()
	{
		return [
			'flags' => $this->flags,
			'keyword' => $this->keyword,
			'position' => $this->position,
			'token' => $this->token,
			'type' => $this->type,
			'typeName' => $this->getTypeName(),
			'value' => $this->value
		];
	}


}
