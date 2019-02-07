<?php
declare(strict_types=1);

namespace Columba\Data;

use Closure;
use Columba\Facade\IArray;
use Columba\Facade\ICountable;
use Columba\Facade\IIterator;
use Columba\Facade\IJson;
use Columba\Util\ArrayUtil;

/**
 * Class Collection
 *
 * @package Columba\Data
 * @author Bas Milius <bas@mili.us>
 * @since 1.4.0
 */
class Collection implements IArray, ICountable, IIterator, IJson
{

	/**
	 * @var array
	 */
	private $items;

	/**
	 * @var int
	 */
	private $position = 0;

	/**
	 * Collection constructor.
	 *
	 * @param array $items
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function __construct(array $items = [])
	{
		$this->items = $items;
	}

	/**
	 * Returns all items.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function all(): array
	{
		return $this->items;
	}

	/**
	 * Appends an item to the collection.
	 *
	 * @param $item
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function append($item): void
	{
		$this->items[] = $item;
	}

	public final function chunk(int $size): self
	{
		$collection = new static;

		foreach (array_chunk($this->items, $size) as $chunk)
			$collection->append(new static($chunk));

		return $collection;
	}

	/**
	 * Collapses the collection.
	 *
	 * @return Collection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function collapse(): self
	{
		$result = [];

		foreach ($this->all() as $values)
		{
			if ($values instanceof self)
				$values = $values->all();

			if (is_array($values))
				$result = array_merge($result, $values);
			else
				$result[] = $values; // Assuming a single value.s
		}

		return new static($result);
	}

	/**
	 * Returns TRUE if an item exists in the collection.
	 *
	 * @param $value
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function contains($value): bool
	{
		if ($value instanceof Closure)
			return !is_null($this->first($value));

		return in_array($value, $this->items);
	}

	/**
	 * Diff the collection.
	 *
	 * @param array|IArray|self $items
	 *
	 * @return Collection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function diff($items): self
	{
		return new static(array_diff($this->items, $this->ensureArray($items)));
	}

	/**
	 * Executes a callable over each item.
	 *
	 * @param callable $fn
	 *
	 * @return Collection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function each(callable $fn): self
	{
		array_map($fn, $this->all());

		return $this;
	}

	/**
	 * Filters the collection.
	 *
	 * @param callable $fn
	 *
	 * @return Collection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function filter(callable $fn): self
	{
		return new static(array_filter($this->items, $fn));
	}

	/**
	 * Returns the first element of the collection passing the truth check.
	 *
	 * @param callable|null $fn
	 * @param mixed         $default
	 *
	 * @return mixed|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function first(?callable $fn = null, $default = null)
	{
		if ($fn === null)
			return count($this) > 0 ? reset($this) : $default;

		return ArrayUtil::first($this->items, $fn, $default);
	}

	/**
	 * Maps a callable over each item in the collection.
	 *
	 * @param callable $fn
	 *
	 * @return Collection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function map(callable $fn): self
	{
		return new static(array_map($fn, $this->items));
	}

	/**
	 * Merges the collection with other items.
	 *
	 * @param array|IArray|self $items
	 *
	 * @return Collection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function merge($items): self
	{
		return new static(array_merge($this->items, $this->ensureArray($items)));
	}

	/**
	 * Returns and removes the last item in the collection.
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function pop()
	{
		return array_pop($this->items);
	}

	/**
	 * Prepends an item to the collection.
	 *
	 * @param $item
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function prepend($item): void
	{
		array_unshift($this->items, $item);
	}

	/**
	 * Reverses the collection.
	 *
	 * @return Collection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function reverse(): self
	{
		return new static(array_reverse($this->items));
	}

	/**
	 * Returns and removes the first element in the collection.
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function shift()
	{
		return array_shift($this->items);
	}

	/**
	 * Shuffles the collection.
	 *
	 * @return Collection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function shuffle(): self
	{
		shuffle($this->items);

		return $this;
	}

	/**
	 * Returns a slice of the collection.
	 *
	 * @param int      $offset
	 * @param int|null $length
	 *
	 * @return Collection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function slice(int $offset, ?int $length = null): self
	{
		return new static(array_slice($this->items, $offset, $length));
	}

	/**
	 * Sorts the collection.
	 *
	 * @param callable $fn
	 *
	 * @return Collection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function sort(callable $fn): self
	{
		usort($this->items, $fn);

		return $this;
	}

	/**
	 * Splice the collection.
	 *
	 * @param int   $offset
	 * @param int   $length
	 * @param mixed ...$replacement
	 *
	 * @return Collection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function splice(int $offset = 0, int $length = 0, ...$replacement): self
	{
		return new static(array_splice($this->items, $offset, $length, $replacement));
	}

	/**
	 * Transforms the collection.
	 *
	 * @param callable $fn
	 *
	 * @return Collection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function transform(callable $fn): self
	{
		$this->items = array_map($fn, $this->items);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function count(): int
	{
		return count($this->all());
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function jsonSerialize(): array
	{
		return $this->toArray();
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function offsetExists($field): bool
	{
		return isset($this->items[$field]);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function offsetGet($field)
	{
		return $this->items[$field] ?? null;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function offsetSet($field, $value): void
	{
		if ($field === null)
			$this->items[] = $value;
		else
			$this->items[$field] = $value;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function offsetUnset($field): void
	{
		array_splice($this->items, $field, 1);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function toArray(): array
	{
		return $this->all();
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function current()
	{
		return $this->items[$this->position];
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function key()
	{
		return $this->position;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function next(): void
	{
		$this->position++;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function rewind(): void
	{
		$this->position = 0;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function valid(): bool
	{
		return isset($this->items[$this->position]);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function __debugInfo(): array
	{
		return $this->items;
	}

	/**
	 * Ensures an array for various functions.
	 *
	 * @param array|IArray|self $items
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	protected function ensureArray($items): array
	{
		if ($items instanceof self)
			return $items->all();

		if ($items instanceof IArray)
			return $items->toArray();

		return $items;
	}

}
