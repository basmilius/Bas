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
use Columba\Foundation\DotEnv\Adapter\IAdapter;
use Columba\Foundation\DotEnv\Adapter\PutEnvAdapter;

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
		PutEnvAdapter::class
	];

	protected $fileContents;
	protected $fileLength;
	protected $fileName;
	protected $vars;

	private $cursor = 0;
	private $keys;
	private $position = 0;

	public function __construct(string $fileName, array $adapters = self::DEFAULT_ADAPTERS)
	{
		if (!is_file($fileName))
			throw new DotEnvException(sprintf('The file "%s" is not readable.', $fileName), DotEnvException::ERR_FILE_NOT_READABLE);

		$this->fileContents = trim(file_get_contents($fileName));
		$this->fileLength = mb_strlen($this->fileContents);
		$this->fileName = $fileName;
		$this->lex();

		$this->keys = array_keys($this->vars);

		/** @var IAdapter[] $adapters */
		$adapters = array_map(function (string $adapter): IAdapter
		{
			return new $adapter();
		}, $adapters);

		foreach ($adapters as $adapter)
			$adapter->adapt($this);
	}

	public function getVars(): array
	{
		return $this->vars;
	}

	protected function addValue(string $name, string $value): void
	{
		$this->vars[$name] = $value;
	}

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

	protected function lexComment(): void
	{
		// We're ignoring comments, so advance the cursor to the end of the comment.
		$this->cursor = mb_strpos($this->fileContents, PHP_EOL, $this->cursor) ?: mb_strlen($this->fileContents);
	}

	protected function lexEmpty(): void
	{
		$this->cursor++;
	}

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

	protected function lexValue(string $name): void
	{
		$end = mb_strpos($this->fileContents, PHP_EOL, $this->cursor);

		if ($end === false)
			throw new DotEnvException(sprintf('Unexpected end of file somewhere near variable "%s" offset %d', $name, $this->cursor), DotEnvException::ERR_SYNTAX_ERROR);

		$this->addValue($name, mb_substr($this->fileContents, $this->cursor, $end - 1 - $this->cursor));
		$this->cursor = $end;
	}

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
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function create(string $directory, string $name = ''): self
	{
		$fileName = rtrim($directory, '/') . DIRECTORY_SEPARATOR . $name . '.env';

		return new static($fileName);
	}

	public function current()
	{
		return $this->vars[$this->key()];
	}

	public function key()
	{
		return $this->keys[$this->position];
	}

	public function next(): void
	{
		$this->position++;
	}

	public function rewind(): void
	{
		$this->position = 0;
	}

	public function valid(): bool
	{
		return isset($this->keys[$this->position]);
	}

}
