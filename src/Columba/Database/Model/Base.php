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

namespace Columba\Database\Model;

use Columba\Database\Error\ModelException;
use Columba\Facade\Arrayable;
use Columba\Facade\ArrayAccessible;
use Columba\Facade\Debuggable;
use Columba\Facade\Jsonable;
use Columba\Facade\ObjectAccessible;
use Serializable;
use function in_array;
use function serialize;
use function sprintf;
use function unserialize;

/**
 * Class Base
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Model
 * @since 1.6.0
 */
abstract class Base implements Arrayable, Jsonable, Debuggable, Serializable
{

	use ArrayAccessible;
	use ObjectAccessible;

	protected static array $immutable = [];
	protected static bool $isImmutable = false;

	protected ?self $copyOf = null;
	protected array $modelData;
	protected bool $isNew;
	protected array $modified = [];

	/**
	 * Base constructor.
	 *
	 * @param array|null $data
	 * @param bool $isNew
	 * @param static|null $copyOf
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function __construct(?array $data = null, bool $isNew = true, ?self $copyOf = null)
	{
		$this->copyOf = $copyOf;
		$this->isNew = $isNew;

		if ($copyOf !== null)
			$this->modelData = &$copyOf->modelData;
		else
			$this->modelData = $data ?? [];

		$this->initialize();
	}

	/**
	 * Initializes the model.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function initialize(): void
	{
		if ($this->isNew)
			return;

		$this->prepare($this->modelData);
	}

	/**
	 * Sets the model's data.
	 *
	 * @param array $data
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function setModelData(array $data): void
	{
		$this->modelData = $data;
	}

	/**
	 * Copies our model and binds it to this instance.
	 *
	 * @param callable $fn
	 *
	 * @return static
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function copyWith(callable $fn): self
	{
		$copy = new static(null, $this->isNew, $this);

		$fn($copy);

		return $copy;
	}

	/**
	 * Gets a value.
	 *
	 * @param string $column
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getOriginalValue(string $column)
	{
		return $this->modelData[$column] ?? null;
	}

	/**
	 * Gets a value.
	 *
	 * @param string $column
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getValue(string $column)
	{
		return $this->modelData[$column] ?? null;
	}

	/**
	 * Returns TRUE if a value exists.
	 *
	 * @param string $column
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function hasValue(string $column): bool
	{
		return isset($this->modelData[$column]);
	}

	/**
	 * Sets a value.
	 *
	 * @param string $column
	 * @param mixed $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function setValue(string $column, $value): void
	{
		if (static::$isImmutable)
			throw new ModelException(sprintf('The model %s is immutable.', static::class), ModelException::ERR_IMMUTABLE);

		if ($this->isImmutable($column))
			throw new ModelException(sprintf('The column %s on model %s is immutable.', $column, static::class), ModelException::ERR_IMMUTABLE);

		$this->modelData[$column] = $value;

		if ($this->hasColumn($column))
			$this->modified[] = $column;
	}

	/**
	 * Unsets a value.
	 *
	 * @param string $column
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function unsetValue(string $column): void
	{
		if (static::$isImmutable)
			throw new ModelException(sprintf('The model %s is immutable.', static::class), ModelException::ERR_IMMUTABLE);

		if ($this->isImmutable($column))
			throw new ModelException(sprintf('The column %s on model %s is immutable.', $column, static::class), ModelException::ERR_IMMUTABLE);

		$this->modelData[$column] = null;

		if ($this->hasColumn($column))
			$this->modified[] = $column;
	}

	/**
	 * Returns TRUE if the given column is immutable.
	 *
	 * @param string $column
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private function isImmutable(string $column): bool
	{
		return static::$isImmutable || in_array($column, static::$immutable);
	}

	/**
	 * Returns TRUE if the given column exists in the table linked to this {@see Model}.
	 *
	 * @param string $column
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public abstract function hasColumn(string $column): bool;

	/**
	 * Prepares the data before the model can be used.
	 *
	 * @param array $data
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected abstract function prepare(array &$data): void;

	/**
	 * Publishes the data to a public something.
	 *
	 * @param array $data
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected abstract function publish(array &$data): void;

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function serialize(): string
	{
		return serialize($this->modelData);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function unserialize($serialized): void
	{
		$this->modelData = unserialize($serialized);
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function jsonSerialize(): array
	{
		$data = $this->toArray();
		$this->publish($data);

		foreach ($data as &$field)
			if ($field instanceof Jsonable)
				$field = $field->jsonSerialize();

		return $data;
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function toArray(): array
	{
		return $this->modelData;
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __debugInfo(): array
	{
		$data = [
			'_meta' => [
				'type' => static::class,
				'immutable' => static::$isImmutable,
				'immutable_columns' => static::$immutable,
				'is_new' => $this->isNew
			]
		];
		$data = array_merge($data, $this->toArray());
		$this->publish($data);

		return $data;
	}

}
