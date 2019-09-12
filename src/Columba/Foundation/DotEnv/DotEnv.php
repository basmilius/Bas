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

namespace Columba\Foundation\DotEnv;

use Columba\Facade\IArray;
use Columba\Facade\IIterator;
use Columba\Facade\IJson;
use Columba\Foundation\DotEnv\Adapter\EnvAdapter;
use Columba\Foundation\DotEnv\Adapter\IAdapter;
use Columba\Foundation\DotEnv\Adapter\PutEnvAdapter;
use Generator;

/**
 * Class DotEnv
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Foundation\DotEnv
 * @since 1.6.0
 */
class DotEnv implements IArray, IIterator, IJson
{

	private const DEFAULT_ADAPTERS = [
		EnvAdapter::class,
		PutEnvAdapter::class
	];

	/**
	 * @var IAdapter[]
	 */
	protected $adapters;

	/**
	 * @var string
	 */
	protected $fileContents;

	/**
	 * @var int
	 */
	protected $fileLength;

	/**
	 * @var string
	 */
	protected $fileName;

	/**
	 * @var string[]
	 */
	protected $vars;

	/**
	 * @var int
	 */
	private $cursor = 0;

	/**
	 * @var string[]
	 */
	private $keys;

	/**
	 * @var int
	 */
	private $position = 0;

	/**
	 * DotEnv constructor.
	 *
	 * @param string   $fileName
	 * @param string[] $adapters
	 *
	 * @throws DotEnvException
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(string $fileName, array $adapters = self::DEFAULT_ADAPTERS)
	{
		if (!is_file($fileName))
			throw new DotEnvException(sprintf('The file "%s" is not readable.', $fileName), DotEnvException::ERR_FILE_NOT_READABLE);

		$this->adapters = iterator_to_array($this->createAdapterInstances($adapters));
		$this->fileContents = trim(file_get_contents($fileName));
		$this->fileLength = mb_strlen($this->fileContents);
		$this->fileName = $fileName;
		$this->lex();

		$this->keys = array_keys($this->vars);
	}

	/**
	 * Adds a found variable.
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function addValue(string $name, string $value): void
	{
		$this->vars[$name] = $value;

		foreach ($this->adapters as $adapter)
			$adapter->set($name, $value);
	}

	/**
	 * Lex the file contents.
	 *
	 * @throws DotEnvException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function lex(): void
	{
		$this->cursor = 0;
		$this->vars = [];

		while ($this->cursor < $this->fileLength)
		{
			$nextChar = mb_substr($this->fileContents, $this->cursor, 1);

			if ($nextChar === PHP_EOL)
				$this->lexEmpty();
			else if ($nextChar === '#')
				$this->lexComment();
			else if (ctype_alpha($nextChar))
				$this->lexVariable();
			else
				$this->cursor++;
		}
	}

	/**
	 * Lexes a comment. E.g. # This is a comment.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function lexComment(): void
	{
		// We're ignoring comments, so advance the cursor to the end of the comment.
		$this->cursor = mb_strpos($this->fileContents, PHP_EOL, $this->cursor) ?: mb_strlen($this->fileContents);
	}

	/**
	 * Lexes an empty line.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function lexEmpty(): void
	{
		$this->cursor++;
	}

	/**
	 * Lexes a variable, both quoted and unquoted are considered here.
	 *
	 * @throws DotEnvException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function lexVariable(): void
	{
		$equalsPosition = mb_strpos($this->fileContents, '=', $this->cursor);

		if ($equalsPosition === false)
			throw new DotEnvException(sprintf('Expected "=" somewhere near offset %d', $this->cursor), DotEnvException::ERR_SYNTAX_ERROR);

		$end = $equalsPosition - $this->cursor;
		$name = mb_substr($this->fileContents, $this->cursor, $end);

		$this->cursor = $equalsPosition + 1;

		if (mb_substr($this->fileContents, $this->cursor, 1) === '"')
			$this->lexQuotedValue($name);
		else
			$this->lexValue($name);
	}

	/**
	 * Lexes a value. E.g. MY_VAR=Hello world!
	 *
	 * @param string $name
	 *
	 * @throws DotEnvException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function lexValue(string $name): void
	{
		$end = mb_strpos($this->fileContents, PHP_EOL, $this->cursor);

		if ($end === false)
			throw new DotEnvException(sprintf('Unexpected end of file somewhere near variable "%s" offset %d', $name, $this->cursor), DotEnvException::ERR_SYNTAX_ERROR);

		$this->addValue($name, mb_substr($this->fileContents, $this->cursor, $end - 1 - $this->cursor));
		$this->cursor = $end;
	}

	/**
	 * Lexes a quoted value. E.g. MY_VAR="This is amazing"
	 *
	 * @param string $name
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function lexQuotedValue(string $name): void
	{
		$this->cursor++;

		$end = $this->cursor;

		while (true)
		{
			$end = mb_strpos($this->fileContents, '"', $end);

			if (mb_substr($this->fileContents, $end - 1, 1) !== '\\')
				break;

			$end++;
		}

		$this->addValue($name, mb_substr($this->fileContents, $this->cursor, $end - $this->cursor));
		$this->cursor = $end;
	}

	/**
	 * Converts adapter classnames into adapter instances.
	 *
	 * @param string[] $adapters
	 *
	 * @return Generator<IAdapter>
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private function createAdapterInstances(array $adapters): Generator
	{
		foreach ($adapters as $adapter)
			yield new $adapter();
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function current()
	{
		return $this->vars[$this->key()];
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function key()
	{
		return $this->keys[$this->position];
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function next(): void
	{
		$this->position++;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function rewind(): void
	{
		$this->position = 0;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function valid(): bool
	{
		return isset($this->keys[$this->position]);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function offsetExists($field): bool
	{
		return isset($this->vars[$field]);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function offsetGet($field)
	{
		return $this->vars[$field];
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function offsetSet($field, $value): void
	{
		throw new DotEnvException('Environment variables are immutable.', DotEnvException::ERR_IMMUTABLE);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function offsetUnset($field): void
	{
		throw new DotEnvException('Environment variables are immutable.', DotEnvException::ERR_IMMUTABLE);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function toArray(): array
	{
		return $this->vars;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function jsonSerialize(): array
	{
		return $this->vars;
	}

	/**
	 * Creates a new {@see DotEnv} instance from directory (and an optional file).
	 *
	 * @param string $directory
	 * @param string $name
	 *
	 * @return static
	 * @throws DotEnvException
	 * @since 1.6.0
	 * @author Bas Milius <bas@mili.us>
	 */
	public static function create(string $directory, string $name = ''): self
	{
		$fileName = rtrim($directory, '/') . DIRECTORY_SEPARATOR . $name . '.env';

		return new static($fileName);
	}

}
