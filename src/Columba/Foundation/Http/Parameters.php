<?php
/**
 * Copyright (c) 2019 - 2020 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Foundation\Http;

use Columba\Facade\Arrayable;
use Columba\Facade\ArrayAccessible;
use Columba\Facade\Debuggable;
use Columba\Facade\EasyAccessible;
use Columba\Facade\IsCountable;
use Columba\Facade\Jsonable;
use Columba\Facade\Loopable;
use Columba\Facade\ObjectAccessible;
use function array_keys;
use function count;

/**
 * Class Parameters
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Foundation\Http
 * @since 1.5.0
 */
class Parameters implements Arrayable, Debuggable, IsCountable, Loopable, Jsonable
{

	use ArrayAccessible;
	use EasyAccessible;
	use ObjectAccessible;

	protected array $data;
	protected int $position = 0;

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
	 * Gets a value.
	 *
	 * @param string $field
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getValue(string $field)
	{
		return $this->data[$field] ?? null;
	}

	/**
	 * Returns TRUE if a value exists.
	 *
	 * @param string $field
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function hasValue(string $field): bool
	{
		return isset($this->data[$field]);
	}

	/**
	 * Sets a value.
	 *
	 * @param string $field
	 * @param mixed $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function setValue(string $field, $value): void
	{
		$this->data[$field] = $value;
	}

	/**
	 * Unsets a value.
	 *
	 * @param string $field
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function unsetValue(string $field): void
	{
		$this->data[$field] = null;
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
		return ($this->keys()[$this->position] ?? null) !== null;
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
	public final function __debugInfo(): array
	{
		return $this->toArray();
	}

}
