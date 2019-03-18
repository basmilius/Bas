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

namespace Columba\Http\Foundation;

use Columba\Facade\GetHasSetUnset;
use Columba\Facade\IArray;
use Columba\Facade\ICountable;
use Columba\Facade\IIterator;
use Columba\Facade\IJson;

/**
 * Class Parameters
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Http\Foundation
 * @since 1.5.0
 */
abstract class Parameters implements IArray, ICountable, IIterator, IJson
{

	use GetHasSetUnset;

	/**
	 * @var array
	 */
	protected $keys;

	/**
	 * @var array
	 */
	protected $values;

	/**
	 * @var int
	 */
	protected $position = 0;

	/**
	 * Parameters constructor.
	 *
	 * @param array $parameters
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function __construct(array $parameters = [])
	{
		$this->keys = array_keys($parameters);
		$this->values = array_values($parameters);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function toArray(): array
	{
		return array_combine($this->keys, $this->values);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function count(): int
	{
		return count($this->keys);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 * @internal
	 */
	public final function current()
	{
		return $this->values[$this->position];
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 * @internal
	 */
	public final function key()
	{
		return $this->keys[$this->position];
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 * @internal
	 */
	public final function next(): void
	{
		$this->position++;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 * @internal
	 */
	public final function rewind(): void
	{
		$this->position = 0;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 * @internal
	 */
	public final function valid(): bool
	{
		return isset($this->keys[$this->position]);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function jsonSerialize(): array
	{
		return $this->toArray();
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 * @internal
	 */
	public final function offsetExists($field): bool
	{
		return in_array($field, $this->keys);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 * @internal
	 */
	public final function offsetGet($field)
	{
		return $this->values[array_search($field, $this->keys)];
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 * @internal
	 */
	public final function offsetSet($field, $value): void
	{
		$this->keys[] = $field;
		$this->values[] = $value;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 * @internal
	 */
	public final function offsetUnset($field): void
	{
		$index = array_search($field, $this->keys);

		unset($this->keys[$index]);
		unset($this->values[$index]);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function __debugInfo(): array
	{
		return $this->toArray();
	}

}
