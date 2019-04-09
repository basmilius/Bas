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

use PDO;
use PDOException;
use PDOStatement;

/**
 * Class AbstractDatabaseDriver
 *
 * @package Columba\Database
 * @author Bas Milius <bas@mili.us>
 * @since 1.0.0
 */
abstract class AbstractDatabaseDriver
{

	use QueryBuilderMethods;

	/**
	 * @var PDO|null
	 */
	protected $pdo = null;

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

		if ($driver->pdo !== null)
			$this->pdo = $driver->pdo;
	}

	/**
	 * Creates a new QueryBuilder instance.
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function createQueryBuilder(): QueryBuilder
	{
		return new QueryBuilder($this);
	}

	/**
	 * Returns error info.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function errorInfo(): array
	{
		return $this->pdo->errorInfo();
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
	 * Gets an attribute.
	 *
	 * @param int $attribute
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function getAttribute(int $attribute)
	{
		return $this->pdo->getAttribute($attribute);
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
		return $this->pdo->lastInsertId($name);
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
	 * Prepares a SQL Query statement.
	 *
	 * @param string $query
	 * @param array  $options
	 * @param bool   $isFile
	 *
	 * @return PreparedStatement
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function prepare(string $query, array $options = [], bool $isFile = false): PreparedStatement
	{
		try
		{
			if ($isFile && strlen($query) < PHP_MAXPATHLEN && is_file($query))
				$query = file_get_contents($query);

			$statement = $this->pdo->prepare($query, $options);

			if (!($statement instanceof PDOStatement))
				throw new PDOException($this->pdo->errorInfo()[2], intval($this->pdo->errorCode()));

			return new PreparedStatement($this, $statement);
		}
		catch (PDOException $err)
		{
			throw new DatabaseException('Query failed!', DatabaseException::ERR_QUERY_FAILED, $err);
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
		return $this->pdo->query($query);
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
		return $this->pdo->quote($value, $type);
	}

	/**
	 * Adds wildcard.
	 *
	 * @param string $value
	 * @param bool   $left
	 * @param bool   $right
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function wildcard(string $value, bool $left, bool $right): array
	{
		$str = '';

		if ($left) $str .= '%';
		$str .= $value;
		if ($right) $str .= '%';

		return [$str, PDO::PARAM_STR];
	}

}
