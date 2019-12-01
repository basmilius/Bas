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

namespace Columba\Database\Dao;

use Columba\Database\DatabaseException;
use Columba\Facade\IArray;
use Columba\Facade\IJson;
use function get_called_class;

/**
 * Class AbstractModel
 *
 * @package Columba\Database\Dao
 * @author Bas Milius <bas@mili.us>
 * @since 1.4.0
 */
abstract class AbstractModel implements IArray, IJson
{

	protected array $data;
	protected bool $dirty = false;

	/**
	 * AbstractModel constructor.
	 *
	 * @param array $data
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function __construct(array $data)
	{
		$this->initialize($data);
	}

	/**
	 * Initializes the model with data.
	 *
	 * @param array $data
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 * @internal
	 */
	public final function initialize(array $data): void
	{
		$this->data = $data;
		$this->dirty = false;
		$this->transformData($this->data);
	}

	/**
	 * Adjusts data for a public api.
	 *
	 * @param array $data
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	protected function forPublicData(array $data): array
	{
		$data['@type'] = get_called_class();

		return $data;
	}

	/**
	 * Transforms data before the model can be used.
	 *
	 * @param array $data
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	protected function transformData(array &$data): void
	{
	}

	/**
	 * Deletes a field.
	 *
	 * @param string $field
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	protected function deleteField(string $field): void
	{
		unset($this->data[$field]);
		$this->dirty = true;
	}

	/**
	 * Gets a field.
	 *
	 * @param string $field
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	protected function getField(string $field)
	{
		return $this->data[$field];
	}

	/**
	 * Returns TRUE if a field exists.
	 *
	 * @param string $field
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	protected function hasField(string $field): bool
	{
		return isset($this->data[$field]);
	}

	/**
	 * Sets a field and marks this model as dirty.
	 *
	 * @param string $field
	 * @param mixed  $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	protected function setField(string $field, $value): void
	{
		$this->data[$field] = $value;
		$this->dirty = true;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function __get(string $field)
	{
		if (!$this->hasField($field))
			throw new DatabaseException(sprintf('Field %s does not exist!', $field), DatabaseException::ERR_FIELD_NOT_FOUND);

		return $this->getField($field);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function __isset($field): bool
	{
		return $this->hasField($field);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function __set(string $field, $value): void
	{
		$this->setField($field, $value);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function __unset($field): void
	{
		$this->deleteField($field);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function jsonSerialize(): array
	{
		return $this->forPublicData($this->data);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function offsetExists($field): bool
	{
		return $this->hasField($field);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function offsetGet($field)
	{
		return $this->getField($field);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function offsetSet($field, $value): void
	{
		$this->setField($field, $value);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function offsetUnset($field): void
	{
		$this->deleteField($field);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function toArray(): array
	{
		return $this->data;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function __debugInfo(): array
	{
		return $this->data;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function __toString(): string
	{
		return get_called_class();
	}

}
