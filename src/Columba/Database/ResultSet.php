<?php
/**
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Database;

use ArrayAccess;
use Countable;
use ErrorException;
use Iterator;
use PDO;
use PDOException;
use PDOStatement;

/**
 * Class ResultSet
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database
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
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct (PreparedStatement $statement, PDOStatement $pdoStatement)
	{
		$this->pdoStatement = $pdoStatement;
		$this->statement = $statement;

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
	public final function var ()
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
	public final function current (): array
	{
		return $this->results[$this->position];
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function next (): void
	{
		$this->position++;
	}

	/**
	 * {@inheritdoc}
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function key (): int
	{
		return $this->position;
	}

	/**
	 * {@inheritdoc}
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function valid (): bool
	{
		return isset($this->results[$this->position]);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function rewind (): void
	{
		$this->position = 0;
	}

	/**
	 * {@inheritdoc}
	 * @throws ErrorException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function offsetExists ($offset): bool
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
	public final function offsetGet ($offset)
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
	public final function offsetSet ($offset, $value)
	{
		throw new ErrorException('ResultSet is immutabe and can therefore not be changed.');
	}

	/**
	 * {@inheritdoc}
	 * @throws ErrorException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function offsetUnset ($offset)
	{
		throw new ErrorException('ResultSet is immutabe and can therefore not be changed.');
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function count (): int
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
	public final function into (string $className, ...$arguments): array
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
	public final function intoSingle (string $className, ...$arguments)
	{
		$all = $this->into($className, ...$arguments);

		return $all[0] ?? null;
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
	public final function rawInto (string $className, ...$arguments): array
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
	public final function rawIntoSingle (string $className, ...$arguments)
	{
		$all = $this->rawInto($className, ...$arguments);

		return $all[0] ?? null;
	}

	/**
	 * Alias for count().
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function rowCount (): int
	{
		return $this->count();
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function __debugInfo (): array
	{
		return [
			'position' => $this->position,
			'results' => $this->results
		];
	}

}
