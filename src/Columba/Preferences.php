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

namespace Columba;

use ArrayAccess;
use Columba\Util\ArrayUtil;
use Countable;
use Iterator;

/**
 * Class Preferences
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba
 * @since 1.0.0
 */
final class Preferences implements ArrayAccess, Countable, Iterator
{

	/**
	 * @var int
	 */
	private $current;

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @var Preferences|null
	 */
	private $parent;

	/**
	 * Preferences constructor.
	 *
	 * @param array            $data
	 * @param Preferences|null $parent
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function __construct (array $data, ?Preferences $parent = null)
	{
		$this->current = 0;
		$this->data = $data;
		$this->parent = $parent;

		$this->loop();
	}

	/**
	 * Loops the data array for underlying instances.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private final function loop (): void
	{
		foreach ($this->data as $key => $value)
		{
			if (!is_array($value) || ArrayUtil::isSequentialArray($value)) // Skip non-array values or sequential arrays.
				continue;

			$this->data[$key] = new self($value, $this);
		}
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function current ()
	{
		return $this->data[array_keys($this->data)[$this->current]];
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function next (): void
	{
		$this->current++;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function key ()
	{
		return array_keys($this->data)[$this->current];
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function valid (): bool
	{
		return isset(array_keys($this->data)[$this->current]);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function rewind ()
	{
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function offsetExists ($offset): bool
	{
		return $offset === -1 || isset($this->data[$offset]);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function offsetGet ($offset)
	{
		if ($offset === -1)
			return $this->parent;

		return $this->data[$offset];
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function offsetSet ($offset, $value): void
	{
		if ($offset === -1)
			throw new \BadMethodCallException('Cannot set parent of Preferences instance.');

		$this->data[$offset] = $value;
		$this->loop();
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function offsetUnset ($offset): void
	{
		if ($offset === -1)
			throw new \BadMethodCallException('Cannot unset parent of Preferences instance.');

		unset($this->data[$offset]);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function count (): int
	{
		return count($this->data);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function __debugInfo (): array
	{
		return [
			'data' => '** Hidden for security reasons. **'
		];
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
	public static function loadFromJson (string $fileName): self
	{
		if (!is_file($fileName) || !is_readable($fileName))
			throw new \InvalidArgumentException('$fileName must be a readable file!');

		$data = json_decode(file_get_contents($fileName), true);

		if ($data === null && json_last_error() !== JSON_ERROR_NONE)
			throw new \InvalidArgumentException('$fileName must be a valid JSON file!');

		return new self($data);
	}

}
