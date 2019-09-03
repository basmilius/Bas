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

namespace Columba\Database;

use Columba\Data\Collection;
use Columba\Database\Dao\Model;
use Columba\Facade\ICountable;
use Columba\Facade\IIterator;
use PDO;
use PDOStatement;

/**
 * Class ResultSet
 *
 * @package Columba\Database
 * @author Bas Milius <bas@mili.us>
 * @since 1.0.0
 */
final class ResultSet implements ICountable, IIterator
{

	/**
	 * @var PDOStatement
	 */
	private $pdoStatement;

	/**
	 * @var PreparedStatement
	 */
	private $statement;

	/**
	 * @var int
	 */
	private $affectedRows;

	/**
	 * @var int
	 */
	private $foundRows;

	/**
	 * @var string|null
	 */
	private $modelClass;

	/**
	 * @var int
	 */
	private $position;

	/**
	 * @var mixed
	 */
	private $currentRow;

	/**
	 * ResultSet constructor.
	 *
	 * @param PreparedStatement $statement
	 * @param PDOStatement      $pdoStatement
	 * @param string|null       $modelClass
	 *
	 * @throws DatabaseException
	 * @since 1.0.0
	 * @author Bas Milius <bas@mili.us>
	 */
	public function __construct(PreparedStatement $statement, PDOStatement $pdoStatement, ?string $modelClass)
	{
		$this->currentRow = null;
		$this->modelClass = $modelClass;
		$this->pdoStatement = $pdoStatement;
		$this->statement = $statement;

		$this->execute();

		$this->affectedRows = $pdoStatement->rowCount();
		$this->foundRows = strstr($this->pdoStatement->queryString, 'SQL_CALC_FOUND_ROWS') ? $statement->getDriver()->foundRows() : 0;
		$this->position = 0;
	}

	/**
	 * Executes the statement.
	 *
	 * @throws DatabaseException
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.6.0
	 */
	private function execute(): void
	{
		$result = $this->pdoStatement->execute();

		if (!$result)
			throw new DatabaseException(strval($this->pdoStatement->errorInfo()[2]), intval($this->pdoStatement->errorCode()));
	}

	/**
	 * {@inheritdoc}
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function current(): ?array
	{
		return $this->currentRow ?? $this->currentRow = $this->pdoStatement->fetch(PDO::FETCH_ASSOC) ?: null;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function next(): void
	{
		$this->currentRow = null;
		$this->position++;
	}

	/**
	 * {@inheritdoc}
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function key(): int
	{
		return $this->position;
	}

	/**
	 * {@inheritdoc}
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function valid(): bool
	{
		return $this->position < $this->affectedRows;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function rewind(): void
	{
		if ($this->position === 0)
			return;

		$this->currentRow = null;
		$this->pdoStatement->closeCursor();
		$this->execute();
		$this->position = 0;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function count(): int
	{
		return $this->affectedRows;
	}

	/**
	 * Tries to convert our results into {@see $className}.
	 *
	 * @param string $className
	 * @param array  $arguments
	 *
	 * @return array
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function into(string $className, ...$arguments): array
	{
		$rows = [];

		$this->rewind();

		do
		{
			$rows[] = $this->intoSingle($className, ...$arguments);
			$this->next();
		}
		while ($this->valid());

		$this->rewind();

		return $rows;
	}

	/**
	 * Tries to convert our results into {@see $className} and returns the first result.
	 *
	 * @param string $className
	 * @param array  $arguments
	 *
	 * @return mixed|null
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function intoSingle(string $className, ...$arguments)
	{
		if (!class_exists($className))
			throw new DatabaseException("Class $className not found!", DatabaseException::ERR_CLASS_NOT_FOUND);

		if (!$this->valid())
			return null;

		$result = $this->current();

		if (!isset($result['id']))
			throw new DatabaseException("Field `id` is not found! Cannot transform into class.", DatabaseException::ERR_FIELD_NOT_FOUND);

		return new $className($result['id'], $result, ...$arguments);
	}

	/**
	 * Converts our results to a collection.
	 *
	 * @return Collection
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function collection(): Collection
	{
		if ($this->modelClass !== null)
			return new Collection($this->models());

		return new Collection($this->toArray());
	}

	/**
	 * Converts our result into a model.
	 *
	 * @return Model|mixed|null
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function model(): ?Model
	{
		if ($this->modelClass === null || !class_exists($this->modelClass) || !is_subclass_of($this->modelClass, Model::class))
			throw new DatabaseException(sprintf('Could not find model %s', $this->modelClass ?? 'NULL'), DatabaseException::ERR_MODEL_NOT_FOUND);

		if (!$this->valid())
			return null;

		/** @var Model|string $modelClass */
		$modelClass = $this->modelClass;
		$result = $this->current();

		if (!isset($result[$modelClass::$primaryKey]))
			return null;

		if (Cache::has($result[$modelClass::$primaryKey], $modelClass))
		{
			$model = Cache::get($result[$modelClass::$primaryKey], $modelClass);
			$model->initialize($result);
			$result = $model;
		}
		else
		{
			$result = new $modelClass($result);
			Cache::set($result);
		}

		return $result;
	}

	/**
	 * Converts our results into models.
	 *
	 * @return Model[]
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function models(): array
	{
		$results = [];

		$this->rewind();

		while ($this->valid())
		{
			$results[] = $this->model();
			$this->next();
		}

		$this->rewind();

		return $results;
	}

	/**
	 * Tries to convert our results into {@see $className}.
	 *
	 * @param string $className
	 * @param array  $arguments
	 *
	 * @return array
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function rawInto(string $className, ...$arguments): array
	{
		$rows = [];

		$this->rewind();

		do
		{
			$rows[] = $this->rawIntoSingle($className, ...$arguments);
			$this->next();
		}
		while ($this->valid());

		$this->rewind();

		return $rows;
	}

	/**
	 * Tries to convert our results into {@see $className} and returns the first result.
	 *
	 * @param string $className
	 * @param array  $arguments
	 *
	 * @return mixed|null
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function rawIntoSingle(string $className, ...$arguments)
	{
		if (!class_exists($className))
			throw new DatabaseException("Class $className not found!", DatabaseException::ERR_CLASS_NOT_FOUND);

		if (!$this->valid())
			return null;

		return new $className($this->current(), ...$arguments);
	}

	/**
	 * Converts our results to an array. If {@see $column} is set, this will return an array of only that column.
	 *
	 * @param string|null $column
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function toArray(?string $column = null): array
	{
		$results = iterator_to_array($this);

		if ($column === null)
			return $results;

		array_walk($results, function (array &$result) use ($column): void
		{
			$result = $result[$column];
		});

		return $results;
	}

	/**
	 * Returns a single result.
	 *
	 * @param string|null $column
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function toSingle(?string $column = null)
	{
		$this->rewind();

		if (!$this->valid())
			return null;

		return $column !== null ? $this->current()[$column] ?? null : $this->current();
	}

	/**
	 * Returns the amount of affected rows.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function affectedRows(): int
	{
		return $this->affectedRows;
	}

	/**
	 * Returns the amount of found rows.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function foundRows(): int
	{
		return $this->foundRows;
	}

	/**
	 * Alias for count().
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function rowCount(): int
	{
		return $this->affectedRows;
	}

	/**
	 * Returns TRUE if this {@see ResultSet} is empty.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function isEmpty(): bool
	{
		return $this->affectedRows === 0;
	}

	/**
	 * Returns TRUE if this {@see ResultSet} has one result.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function hasOne(): bool
	{
		return $this->affectedRows === 1;
	}

	/**
	 * Returns TRUE if this {@see ResultSet} has at least {@see $num} results.
	 *
	 * @param int $count
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function hasAtLeast(int $count): bool
	{
		return $this->affectedRows >= $count;
	}

}
