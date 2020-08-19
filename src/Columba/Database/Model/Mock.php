<?php
declare(strict_types=1);

namespace Columba\Database\Model;

use Columba\Database\Error\ModelException;
use Columba\Facade\Debuggable;
use Columba\Facade\Gettable;
use Columba\Facade\IArray;
use Columba\Facade\IJson;
use Columba\Facade\IsSettable;
use Columba\Facade\Settable;
use Columba\Facade\Unsettable;
use Columba\Util\ArrayUtil;

/**
 * Class Mock
 *
 * @mixin Model
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Model
 * @since 1.6.0
 */
final class Mock implements IArray, IJson, Debuggable, Gettable, IsSettable, Settable, Unsettable
{

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
			if (($key = \array_search($column, $this->visible)) !== false)
				unset($this->visible[$key]);

			if ($this->model->hasColumn($column) && !\in_array($column, $this->hidden))
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
			if (($key = \array_search($column, $this->hidden)) !== false)
				unset($this->hidden[$key]);

			if (!$this->model->hasColumn($column) && !\in_array($column, $this->visible))
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
		if (!\method_exists($this->model, $method))
			throw new ModelException(sprintf('Method "%s" does not exist on either this mock or the linked model.', $method), ModelException::ERR_BAD_METHOD_CALL);

		return $this->model->{$method}(...$arguments);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function __get(string $name)
	{
		return $this->model->__get($name);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function __isset(string $name): bool
	{
		return $this->model->__isset($name);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function __set(string $name, $value): void
	{
		$this->model->__set($name, $value);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function __unset(string $name): void
	{
		$this->model->__unset($name);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function offsetExists($field): bool
	{
		return $this->model->offsetExists($field);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function offsetGet($field)
	{
		return $this->model->offsetGet($field);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function offsetSet($field, $value): void
	{
		$this->model->offsetSet($field, $value);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function offsetUnset($field): void
	{
		$this->model->offsetUnset($field);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function toArray(): array
	{
		$data = $this->mockCall('toArray');

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
		$data = $this->mockCall('toArray');

		$this->model->mockCall('publish', $data);
		$this->resolveVisibilityColumns($data);

		return $data;
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
		foreach (\array_keys($this->macros) as $macro)
			if (\in_array($macro, $this->visible) || $this->model->hasColumn($macro))
				$data[$macro] = $this->model->resolveMacro($macro);

		foreach (\array_keys($this->relationships) as $relation)
			if (\in_array($relation, $this->visible))
				$data[$relation] = $this->model->getValue($relation);

		foreach ($this->hidden as $column)
			unset($data[$column]);
	}

}