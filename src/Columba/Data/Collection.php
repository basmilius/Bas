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

namespace Columba\Data;

use Closure;
use Columba\Facade\IArray;
use Columba\Facade\ICountable;
use Columba\Facade\IIterator;
use Columba\Facade\IJson;
use Columba\Util\ArrayUtil;
use Traversable;
use function array_chunk;
use function array_column;
use function array_diff;
use function array_filter;
use function array_map;
use function array_merge;
use function array_pop;
use function array_reverse;
use function array_shift;
use function array_slice;
use function array_splice;
use function array_sum;
use function array_unshift;
use function count;
use function in_array;
use function is_array;
use function is_null;
use function iterator_to_array;
use function reset;
use function shuffle;
use function usort;

/**
 * Class Collection
 *
 * @package Columba\Data
 * @author Bas Milius <bas@mili.us>
 * @since 1.4.0
 */
class Collection implements IArray, ICountable, IIterator, IJson
{

	private array $items;
	private int $position = 0;

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
	public function all(): array
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
	public function append($item): void
	{
		$this->items[] = $item;
	}

	/**
	 * Chunks the collection.
	 *
	 * @param int $size
	 *
	 * @return Collection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function chunk(int $size): self
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
	public function collapse(): self
	{
		$result = [];

		foreach ($this->all() as $values)
		{
			if ($values instanceof self)
				$values = $values->all();

			if (is_array($values))
				$result = array_merge($result, $values);
			else
				$result[] = $values;
		}

		return new static($result);
	}

	/**
	 * Gets a collection column as a new collection.
	 *
	 * @param mixed $column
	 *
	 * @return Collection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function column(string ...$column): self
	{
		$items = $this->items;

		foreach ($column as $col)
			$items = array_column($items, $col);

		return new static($items);
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
	public function contains($value): bool
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
	public function diff($items): self
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
	public function each(callable $fn): self
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
	public function filter(callable $fn): self
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
	public function first(?callable $fn = null, $default = null)
	{
		if ($fn === null)
			return $this->count() > 0 ? reset($this) : $default;

		return ArrayUtil::first($this->items, $fn, $default);
	}

	/**
	 * Groups the data with the given predicate.
	 *
	 * @param callable $fn
	 *
	 * @return Collection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function groupBy(callable $fn): self
	{
		$items = [];

		foreach ($this->items as $item)
			$items[$fn($item)][] = $item;

		return new static(array_values($items));
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
	public function map(callable $fn): self
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
	public function merge($items): self
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
	public function pop()
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
	public function prepend($item): void
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
	public function reverse(): self
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
	public function shift()
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
	public function shuffle(): self
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
	public function slice(int $offset, ?int $length = null): self
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
	public function sort(callable $fn): self
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
	public function splice(int $offset = 0, int $length = 0, ...$replacement): self
	{
		return new static(array_splice($this->items, $offset, $length, $replacement));
	}

	/**
	 * Sums the collection.
	 *
	 * @return float|int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function sum()
	{
		return array_sum($this->items);
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
	public function transform(callable $fn): self
	{
		$this->items = array_map($fn, $this->items);

		return $this;
	}

	/**
	 * Copies the Collection to a new instance.
	 *
	 * @return Collection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function copy(): self
	{
		return new static($this->items);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function count(): int
	{
		return count($this->items);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function offsetExists($field): bool
	{
		return isset($this->items[$field]);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function offsetGet($field)
	{
		return $this->items[$field] ?? null;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function offsetSet($field, $value): void
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
	public function offsetUnset($field): void
	{
		array_splice($this->items, $field, 1);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function toArray(): array
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
		++$this->position;
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
	public function __debugInfo(): array
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

	/**
	 * Creates a collection from an iterable.
	 *
	 * @param iterable $items
	 *
	 * @return Collection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function from(iterable $items): Collection
	{
		if ($items instanceof Traversable)
			$items = iterator_to_array($items);

		return new static($items);
	}

}
