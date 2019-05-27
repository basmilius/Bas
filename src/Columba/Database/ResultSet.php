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

use ArrayAccess;
use Columba\Data\Collection;
use Columba\Database\Dao\Model;
use Countable;
use ErrorException;
use Iterator;
use PDO;
use PDOException;
use PDOStatement;

/**
 * Class ResultSet
 *
 * @package Columba\Database
 * @author Bas Milius <bas@mili.us>
 * @since 1.0.0
 */
final class ResultSet implements ArrayAccess, Countable, Iterator
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
	 * @var array
	 */
	private $results;

	/**
	 * ResultSet constructor.
	 *
	 * @param PreparedStatement $statement
	 * @param PDOStatement      $pdoStatement
	 * @param string|null       $modelClass
	 *
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct(PreparedStatement $statement, PDOStatement $pdoStatement, ?string $modelClass)
	{
		$this->modelClass = $modelClass;
		$this->pdoStatement = $pdoStatement;
		$this->statement = $statement;

		$this->affectedRows = $pdoStatement->rowCount();
		$this->foundRows = strstr($this->pdoStatement->queryString, 'SQL_CALC_FOUND_ROWS') ? $statement->getDriver()->prepare('SELECT FOUND_ROWS() AS found_rows')->execute()[0]['found_rows'] : 0;
		$this->position = 0;
		$this->results = $this->pdoStatement->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Returns the first and probably only var.
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function var()
	{
		if ($this->count() > 0)
			return array_values($this->results[0])[0];

		throw new PDOException('Did not have any results.');
	}

	/**
	 * {@inheritdoc}
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function current(): array
	{
		return $this->results[$this->position];
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function next(): void
	{
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
		return isset($this->results[$this->position]);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function rewind(): void
	{
		$this->position = 0;
	}

	/**
	 * {@inheritdoc}
	 * @throws ErrorException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function offsetExists($offset): bool
	{
		if (!is_int($offset))
			throw new ErrorException('Offset must be instance of int.');

		return isset($this->results[$offset]);
	}

	/**
	 * {@inheritdoc}
	 * @throws ErrorException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function offsetGet($offset)
	{
		if (!is_int($offset))
			throw new ErrorException('Offset must be instance of int.');

		return $this->results[$offset];
	}

	/**
	 * {@inheritdoc}
	 * @throws ErrorException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function offsetSet($offset, $value)
	{
		throw new ErrorException('ResultSet is immutabe and can therefore not be changed.');
	}

	/**
	 * {@inheritdoc}
	 * @throws ErrorException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function offsetUnset($offset)
	{
		throw new ErrorException('ResultSet is immutabe and can therefore not be changed.');
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function count(): int
	{
		return count($this->results);
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
		if (!class_exists($className))
			throw new DatabaseException("Class $className not found!", DatabaseException::ERR_CLASS_NOT_FOUND);

		$rows = [];

		foreach ($this->results as $result)
		{
			if (!isset($result['id']))
				throw new DatabaseException("Field `id` is not found! Cannot transform into class.", DatabaseException::ERR_FIELD_NOT_FOUND);

			$rows[] = new $className($result['id'], $result, ...$arguments);
		}

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
		$all = $this->into($className, ...$arguments);

		return $all[0] ?? null;
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
		return $this->models()[0] ?? null;
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
		if ($this->modelClass === null || !class_exists($this->modelClass) || !is_subclass_of($this->modelClass, Model::class))
			throw new DatabaseException(sprintf('Could not find model %s', $this->modelClass ?? 'NULL'), DatabaseException::ERR_MODEL_NOT_FOUND);

		/** @var Model|string $modelClass */
		$modelClass = $this->modelClass;
		$results = $this->toArray();

		foreach ($results as &$result)
		{
			if (!isset($result[$modelClass::$primaryKey]))
				continue;

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
		}

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
		if (!class_exists($className))
			throw new DatabaseException("Class $className not found!", DatabaseException::ERR_CLASS_NOT_FOUND);

		$rows = [];

		foreach ($this->results as $result)
			$rows[] = new $className($result, ...$arguments);

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
		$all = $this->rawInto($className, ...$arguments);

		return $all[0] ?? null;
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
		$results = $this->results;

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
		$results = $this->toArray($column);

		return $results[0] ?? null;
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
		return $this->count();
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
		return $this->rowCount() === 0;
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
		return $this->rowCount() === 1;
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
		return $this->rowCount() >= $count;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function __debugInfo(): array
	{
		return [
			'position' => $this->position,
			'results' => $this->results
		];
	}

}
