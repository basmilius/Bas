<?php
declare(strict_types=1);

namespace Columba\Database\Model;

use Columba\Database\Error\ModelException;
use Columba\Facade\Arrayable;
use Columba\Facade\ArrayAccessible;
use Columba\Facade\Debuggable;
use Columba\Facade\Jsonable;
use Columba\Facade\ObjectAccessible;
use Columba\Util\ArrayUtil;
use Serializable;
use stdClass;
use function array_keys;
use function array_search;
use function in_array;
use function method_exists;
use function serialize;
use function unserialize;

/**
 * Class Mock
 *
 * @mixin Model
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Model
 * @since 1.6.0
 */
final class Mock extends stdClass implements Arrayable, Jsonable, Debuggable, Serializable
{

	use ArrayAccessible;
	use ObjectAccessible;

	private Model $model;

	private array $hidden;
	private array $visible;

	private array $macros;
	private array $relationships;

	/**
	 * Mock constructor.
	 *
	 * @param Model $model
	 * @param array $hidden
	 * @param array $visible
	 * @param array $macros
	 * @param array $relationships
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(Model $model, array $hidden, array $visible, array $macros, array $relationships)
	{
		$this->model = $model;

		$this->hidden = $hidden;
		$this->visible = $visible;

		$this->macros = $macros;
		$this->relationships = $relationships;
	}

	/**
	 * Marks the given columns as hidden.
	 *
	 * @param string[] $columns
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function makeHidden(array $columns): self
	{
		foreach ($columns as $column)
		{
			if (($key = array_search($column, $this->visible)) !== false)
				unset($this->visible[$key]);

			if (!in_array($column, $this->hidden))
				$this->hidden[] = $column;
		}

		return $this;
	}

	/**
	 * Marks the given columns as visible.
	 *
	 * @param string[] $columns
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function makeVisible(array $columns): self
	{
		foreach ($columns as $column)
		{
			if (($key = array_search($column, $this->hidden)) !== false)
				unset($this->hidden[$key]);

			if (!in_array($column, $this->visible))
				$this->visible[] = $column;
		}

		return $this;
	}

	/**
	 * Returns only the given columns of the model instance.
	 *
	 * @param array $columns
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function only(array $columns): array
	{
		return ArrayUtil::only($this->toArray(), $columns);
	}

	/**
	 * Invoked when a missing method, probably on our linked {@see Model}, is called.
	 *
	 * @param string $method
	 * @param $arguments
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function __call(string $method, $arguments)
	{
		if (!method_exists($this->model, $method))
			throw new ModelException(sprintf('Method "%s" does not exist on either this mock or the linked model.', $method), ModelException::ERR_BAD_METHOD_CALL);

		return $this->model->{$method}(...$arguments);
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
		return $this->model->getValue($column);
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
		return $this->model->hasValue($column);
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
		$this->model->setValue($column, $value);
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
		$this->model->unsetValue($column);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function toArray(): array
	{
		$data = $this->model->toArray();

		$this->resolveVisibilityColumns($data);

		return $data;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function jsonSerialize(): array
	{
		$data = $this->model->jsonSerialize();

		$this->resolveVisibilityColumns($data);

		foreach ($data as &$field)
			if ($field instanceof Jsonable)
				$field = $field->jsonSerialize();

		return $data;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function serialize(): string
	{
		return serialize([
			$this->model,
			$this->hidden,
			$this->visible
		]);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function unserialize($serialized): void
	{
		/** @var Model $model */
		/** @var array $hidden */
		/** @var array $visible */
		[$model, $hidden, $visible] = unserialize($serialized);
		$model::prepareModel();

		$mock = $model->mock();

		$this->model = $mock->model;
		$this->hidden = $hidden;
		$this->visible = $visible;
		$this->macros = $mock->macros;
		$this->relationships = $mock->relationships;

		unset($mock);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function __debugInfo(): ?array
	{
		return $this->model->__debugInfo();
	}

	/**
	 * Resolves the visibility columns.
	 *
	 * @param array $data
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private final function resolveVisibilityColumns(array &$data): void
	{
		foreach (array_keys($this->macros) as $macro)
			if (in_array($macro, $this->visible) || $this->model->hasColumn($macro))
				$data[$macro] = $this->model->resolveMacro($macro);

		foreach (array_keys($this->relationships) as $relation)
			if (in_array($relation, $this->visible))
				$data[$relation] = $this->model->getValue($relation);

		foreach ($this->hidden as $column)
			unset($data[$column]);
	}

}
