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

use Columba\Util\ReflectionUtil;
use InvalidArgumentException;

/**
 * Class TypedCollection
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Data
 * @since 1.4.0
 */
class TypedCollection extends Collection
{

	private string $type = '';

	/**
	 * TypedCollection constructor.
	 *
	 * @param array $items
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function __construct(array $items = [])
	{
		parent::__construct([]);

		foreach ($items as $item)
			$this->append($item);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function append($item): void
	{
		$this->validateItem($item);

		parent::append($item);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function merge($items): Collection
	{
		foreach ($this->ensureArray($items) as $item)
			$this->validateItem($item);

		return parent::merge($items);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function prepend($item): void
	{
		$this->validateItem($item);

		parent::prepend($item);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function offsetSet($field, $value): void
	{
		$this->validateItem($value);

		parent::offsetSet($field, $value);
	}

	/**
	 * Validates the type of an item.
	 *
	 * @param mixed $item
	 *
	 * @throws InvalidArgumentException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	private function validateItem($item): void
	{
		$type = ReflectionUtil::getType($item);

		if ($this->type === '')
			$this->type = $type;
		else if ($this->type !== $type)
			throw new InvalidArgumentException(sprintf('Item needs to be an instance of %s, %s given.', $this->type, $type));
	}

}
