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

use ArrayAccess;
use Countable;
use Iterator;

/**
 * Class TokenList
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Lexer
 * @since 1.0.0
 */
final class TokenList implements ArrayAccess, Countable, Iterator
{

	/**
	 * @var Token[]
	 */
	private $tokens = [];

	/**
	 * @var int
	 */
	private $count = 0;

	/**
	 * @var int
	 */
	private $idx = 0;

	/**
	 * @var int
	 */
	private $position;

	/**
	 * TokenList constructor.
	 *
	 * @param Token[] $tokens
	 * @param int     $count
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct (array $tokens = [], int $count = -1)
	{
		$this->position = 0;

		if (!empty($tokens))
		{
			$this->tokens = $tokens;
			if ($count === -1)
				$this->count = count($tokens);
		}
	}

	/**
	 * Adds a new {@see Token}.
	 *
	 * @param Token $token
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function add (Token $token): void
	{
		$this->tokens[$this->count++] = $token;
	}

	/**
	 * Gets the HTML for this {@see TokenList}.
	 *
	 * @return string
	 * @throws \ReflectionException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getHtml (): string
	{
		$tokens = [];

		foreach ($this->tokens as $token)
			$tokens[] = $token->getHtml();

		return '<div class="bm-lexer bm-lexer-sql" data-syntax="sql">' . implode($tokens) . '</div>';
	}

	/**
	 * Gets the next {@see Token}. Skips any irrelevant tokens (whitespaces and comments).
	 *
	 * @return Token|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getNext (): ?Token
	{
		for (; $this->idx < $this->count; ++$this->idx)
			if ($this->tokens[$this->idx]->getType() !== Type::WHITESPACE && $this->tokens[$this->idx]->getType() !== Type::COMMENT)
				return $this->tokens[$this->idx];

		return null;
	}

	/**
	 * Gets the next {@see Token} with a given {@see $type}.
	 *
	 * @param int $type
	 *
	 * @return Token|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getNextOfType (int $type): ?Token
	{
		for (; $this->idx < $this->count; ++$this->idx)
			if ($this->tokens[$this->idx]->getType() === $type)
				return $this->tokens[$this->idx];

		return null;
	}

	/**
	 * Gets the next {@see Token} with a given {@see $type} and {@see $value}.
	 *
	 * @param int    $type
	 * @param string $value
	 *
	 * @return Token|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getNextOfTypeAndValue (int $type, string $value): ?Token
	{
		for (; $this->idx < $this->count; ++$this->idx)
			if ($this->tokens[$this->idx]->getType() === $type && $this->tokens[$this->idx]->getValue() === $value)
				return $this->tokens[$this->idx];

		return null;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function offsetExists ($offset): bool
	{
		return $offset < $this->count;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function offsetGet ($offset)
	{
		return $offset < $this->count ? $this->tokens[$offset] : null;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function offsetSet ($offset, $value): void
	{
		if ($offset === null)
			$this->tokens[$this->count++] = $value;
		else
			$this->tokens[$offset] = $value;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function offsetUnset ($offset): void
	{
		unset($this->tokens[$offset]);
		--$this->count;

		for ($i = $offset; $i < $this->count; ++$i)
			$this->tokens[$i] = $this->tokens[$i + 1];

		unset($this->tokens[$this->count]);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function count (): int
	{
		return $this->count;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function current ()
	{
		return $this->tokens[$this->position];
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function next ()
	{
		$this->position++;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function key ()
	{
		return $this->position;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function valid ()
	{
		return isset($this->tokens[$this->position]);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function rewind ()
	{
		$this->position = 0;
	}


	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function __debugInfo (): array
	{
		return $this->tokens;
	}

	/**
	 * Builds an array of of tokens by merging their raw value.
	 *
	 * @param $list
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function build ($list): string
	{
		if (is_string($list))
			return $list;

		if ($list instanceof self)
			$list = $list->tokens;

		$return = '';

		if (is_array($list))
			foreach ($list as $token)
				$return .= $token->getToken();

		return $return;
	}

}
