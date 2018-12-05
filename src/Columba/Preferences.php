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

namespace Columba;

use ArrayAccess;
use Columba\Util\ArrayUtil;
use Countable;
use InvalidArgumentException;
use Iterator;
use JsonSerializable;

/**
 * Class Preferences
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba
 * @since 1.0.0
 */
final class Preferences implements ArrayAccess, Countable, Iterator, JsonSerializable
{

	/**
	 * @var Preferences|null
	 */
	private $parent;

	/**
	 * @var int
	 */
	private $position;

	/**
	 * @var string[]
	 */
	private $keys;

	/**
	 * @var mixed[]
	 */
	private $values;

	/**
	 * Preferences constructor.
	 *
	 * @param array            $data
	 * @param Preferences|null $parent
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function __construct(array $data, ?Preferences $parent = null)
	{
		$this->parent = $parent;
		$this->position = 0;

		$this->keys = array_keys($data);
		$this->values = array_values($data);

		$this->loop();
	}

	/**
	 * Loops the data array for underlying instances.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private final function loop(): void
	{
		foreach ($this->keys as $index => $key)
		{
			if (!is_array($this->values[$index]) || ArrayUtil::isSequentialArray($this->values[$index]))
				continue;

			$this->values[$index] = new self($this->values[$index], $this);
		}
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function current()
	{
		return $this->values[$this->position];
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function next(): void
	{
		$this->position++;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function key(): string
	{
		return $this->keys[$this->position];
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function valid(): bool
	{
		return isset($this->keys[$this->position]);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function rewind(): void
	{
		$this->position = 0;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function offsetExists($offset): bool
	{
		return in_array($offset, $this->keys);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function offsetGet($offset)
	{
		if ($offset === -1)
			return $this->parent;

		return $this->values[$this->findIndex($this->keys, $offset)];
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function offsetSet($offset, $value): void
	{
		$this->values[$this->findIndex($this->keys, $offset)] = $value;
		$this->loop();
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function offsetUnset($offset): void
	{
		unset($this->values[$this->findIndex($this->keys, $offset)]);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function count(): int
	{
		return count($this->keys);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function jsonSerialize(): array
	{
		return array_combine($this->keys, $this->values);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function __debugInfo(): array
	{
		return [
			'data' => '** Hidden for security reasons. **'
		];
	}

	/**
	 * Finds an index in an array.
	 *
	 * @param array $array
	 * @param mixed $value
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	private function findIndex(array $array, $value): int
	{
		return array_search($value, $array);
	}

	/**
	 * Creates a new {@see Preferences} instance from JSON.
	 *
	 * @param string $fileName
	 *
	 * @return Preferences
	 * @author Bas Milius <bas@mili.us>
	 * @since 3.0.0
	 */
	public static function loadFromJson(string $fileName): self
	{
		if (!is_readable($fileName))
			throw new InvalidArgumentException('$fileName must be a readable file!');

		$data = json_decode(file_get_contents($fileName), true);

		if ($data === null && json_last_error() !== JSON_ERROR_NONE)
			throw new InvalidArgumentException('$fileName must be a valid JSON file!');

		return new self($data);
	}

}
