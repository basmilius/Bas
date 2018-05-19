<?php
/**
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Database;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Class AbstractDatabaseDriver
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database
 * @since 1.0.0
 */
abstract class AbstractDatabaseDriver
{

	/**
	 * @var DatabaseDriver
	 */
	protected $driver;

	/**
	 * @var PDO|null
	 */
	private $pdo = null;

	/**
	 * AbstractDatabaseDriver constructor.
	 *
	 * @param DatabaseDriver $driver
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	protected function __construct(DatabaseDriver $driver)
	{
		$this->driver = $driver;

		if ($driver->pdo() !== null)
			$this->pdo($driver->pdo());
	}

	/**
	 * Gets or Sets the PDO instance.
	 *
	 * @param PDO|null $pdo
	 *
	 * @return PDO
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	protected final function pdo(?PDO $pdo = null): ?PDO
	{
		if ($pdo !== null)
			$this->pdo = $pdo;

		return $this->pdo;
	}

	/**
	 * Ping the server.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function ping(): bool
	{
		try
		{
			$this->pdo->query('SELECT 1');

			return true;
		}
		catch (PDOException $err)
		{
			return false;
		}
	}

	/**
	 * Creates a DELETE query.
	 *
	 * @param string $table
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function delete(string $table): QueryBuilder
	{
		$builder = new QueryBuilder($this);
		$builder->delete($table);

		return $builder;
	}

	/**
	 * Creates a DELETE FROM query.
	 *
	 * @param string $table
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function deleteFrom(string $table): QueryBuilder
	{
		$builder = new QueryBuilder($this);
		$builder->deleteFrom($table);

		return $builder;
	}

	/**
	 * Creates an INSERT IGNORE INTO query.
	 *
	 * @param string $table
	 * @param string ...$fields
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function insertIgnoreInto(string $table, string ...$fields): QueryBuilder
	{
		$builder = new QueryBuilder($this);
		$builder->insertIgnoreInto($table, ...$fields);

		return $builder;
	}

	/**
	 * Creates an INSERT INTO query.
	 *
	 * @param string $table
	 * @param string ...$fields
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function insertInto(string $table, string ...$fields): QueryBuilder
	{
		$builder = new QueryBuilder($this);
		$builder->insertInto($table, ...$fields);

		return $builder;
	}

	/**
	 * Creates an INSERT INTO (...) VALUES (...) query.
	 *
	 * @param string $table
	 * @param array  ...$data
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function insertIntoValues(string $table, array ...$data): QueryBuilder
	{
		$builder = new QueryBuilder($this);
		$builder->insertIntoValues($table, ...$data);

		return $builder;
	}

	/**
	 * Creates an OPTIMIZE TABLE query.
	 *
	 * @param string ...$table
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function optimizeTable(string ...$table): QueryBuilder
	{
		$builder = new QueryBuilder($this);
		$builder->optimizeTable(...$table);

		return $builder;
	}

	/**
	 * Creates a SELECT query.
	 *
	 * @param array ...$fields
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function select(...$fields): QueryBuilder
	{
		$builder = new QueryBuilder($this);
		$builder->select(...$fields);

		return $builder;
	}

	/**
	 * Creates a SELECT DISTINCT query.
	 *
	 * @param array ...$fields
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function selectDistinct(...$fields): QueryBuilder
	{
		$builder = new QueryBuilder($this);
		$builder->selectDistinct(...$fields);

		return $builder;
	}

	/**
	 * Creates a SELECT SQL_CALC_FOUND_ROWS query.
	 *
	 * @param array ...$fields
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function selectFoundRows(...$fields): QueryBuilder
	{
		$builder = new QueryBuilder($this);
		$builder->selectFoundRows(...$fields);

		return $builder;
	}

	/**
	 * Creates a TRUNCATE TABLE query.
	 *
	 * @param string $table
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function truncateTable(string $table): QueryBuilder
	{
		$builder = new QueryBuilder($this);
		$builder->truncateTable($table);

		return $builder;
	}

	/**
	 * Creates an UPDATE query.
	 *
	 * @param string $table
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function update(string $table): QueryBuilder
	{
		$builder = new QueryBuilder($this);
		$builder->update($table);

		return $builder;
	}

	/**
	 * Quotes a value.
	 *
	 * @param string $value
	 * @param int    $type
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function quote(string $value, int $type = PDO::PARAM_STR): string
	{
		return $this->pdo()->quote($value, $type);
	}

	/**
	 * Adds wildcard.
	 *
	 * @param string $value
	 * @param bool   $left
	 * @param bool   $right
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function wildcard(string $value, bool $left, bool $right): string
	{
		$str = '';

		if ($left) $str .= '%';
		$str .= $value;
		if ($right) $str .= '%';

		return $str;
	}

	/**
	 * Executes a MySQL {@see $statement} and returns the affected rows.
	 *
	 * @param string $statement
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function exec(string $statement): int
	{
		return $this->pdo->exec($statement);
	}

	/**
	 * Returns the amount of found rows. SQL_CALC_FOUND_ROWS must be present in previous query.
	 *
	 * @return int
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function foundRows(): int
	{
		return $this->prepare('SELECT FOUND_ROWS() AS found_rows')->execute()[0]['found_rows'];
	}

	/**
	 * Gets the last insert ID.
	 *
	 * @param string|null $name
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function lastInsertId(?string $name = null): string
	{
		return $this->pdo()->lastInsertId($name);
	}

	/**
	 * Gets the last insert ID.
	 *
	 * @param string|null $name
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function lastInsertIdInteger(?string $name = null): int
	{
		return intval($this->lastInsertId($name));
	}

	/**
	 * Prepares a SQL Query statement.
	 *
	 * @param string $query
	 * @param array  $options
	 *
	 * @return PreparedStatement
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function prepare(string $query, array $options = []): PreparedStatement
	{
		try
		{
			if (is_file($query))
				$query = file_get_contents($query);

			$statement = $this->pdo()->prepare($query, $options);

			if (!($statement instanceof PDOStatement))
				throw new PDOException($this->pdo()->errorInfo()[2], intval($this->pdo()->errorCode()));

			return new PreparedStatement($this, $statement);
		}
		catch (PDOException $err)
		{
			throw new DatabaseException('Query failed!', DatabaseException::ERR_QUERY_FAILED, $err, $query);
		}
	}

	/**
	 * Executes a raw query.
	 *
	 * @param string $query
	 *
	 * @return PDOStatement
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function query(string $query): PDOStatement
	{
		return $this->pdo()->query($query);
	}

	/**
	 * Prints the errors.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function printErrors(): void
	{
		if (function_exists('pre_die'))
			pre_die($this->pdo()->errorCode(), $this->pdo()->errorInfo());
		else
			print_r([$this->pdo()->errorCode(), $this->pdo()->errorInfo()]);
	}

}
