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

namespace Columba\Foundation\Http;

use Columba\Facade\GetHasSetUnset;
use Columba\Facade\IArray;
use Columba\Facade\ICountable;
use Columba\Facade\IIterator;
use Columba\Facade\IJson;

/**
 * Class Parameters
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Foundation\Http
 * @since 1.5.0
 */
class Parameters implements IArray, ICountable, IIterator, IJson
{

	use GetHasSetUnset;

	/**
	 * @var array
	 */
	protected $data;

	/**
	 * @var int
	 */
	protected $position = 0;

	/**
	 * Parameters constructor.
	 *
	 * @param array $data
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function __construct(array $data = [])
	{
		$this->data = $data;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function toArray(): array
	{
		return $this->data;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function count(): int
	{
		return count($this->data);
	}

	/**
	 * Gets all the keys.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function keys(): array
	{
		return array_keys($this->data);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 * @internal
	 */
	public final function current()
	{
		return $this[$this->key()] ?? null;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 * @internal
	 */
	public final function key()
	{
		return $this->keys()[$this->position] ?? null;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 * @internal
	 */
	public final function next(): void
	{
		++$this->position;
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
		return $this->keys()[$this->position] ?? null !== null;
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
		return isset($this->data[$field]);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 * @internal
	 */
	public final function offsetGet($field)
	{
		return $this->data[$field] ?? null;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 * @internal
	 */
	public final function offsetSet($field, $value): void
	{
		$this->data[$field] = $value;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 * @internal
	 */
	public final function offsetUnset($field): void
	{
		unset($this->data[$field]);
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
