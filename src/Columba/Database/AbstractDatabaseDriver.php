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
	 * Guesses the param type based on value.
	 *
	 * @param mixed $value
	 *
	 * @return array|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 * @example guessValue('hello world') => ['hello world', PDO::PARAM_STR].
	 */
	public final function guessValue($value): ?array
	{
		if ($value === null)
			return [null, PDO::PARAM_NULL, 'null'];

		if (is_array($value))
			return $value; // This should be valid...?

		if (is_int($value) || (is_numeric($value) && intval($value) == floatval($value)))
			return [intval($value), PDO::PARAM_INT, 'int'];

		if (is_float($value) || is_numeric($value))
			return [floatval($value), PDO::PARAM_STR, 'string'];

		if (is_bool($value))
			return [$value ? 1 : 0, PDO::PARAM_INT, 'int'];

		if (is_string($value))
			return [$value, PDO::PARAM_STR, 'string'];

		return null;
	}

	/**
	 * Guesses the param type for all given values based on value.
	 *
	 * @param mixed[] $values
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 * @example guessValues(['hello world', 10]) => [['hello world', PDO::PARAM_STR], [10, PDO::PARAM_INT]].
	 */
	public final function guessValues(array $values): array
	{
		return array_map([$this, 'guessValue'], $values);
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
	 * Calls a function.
	 *
	 * @param string $name
	 * @param mixed  ...$params
	 *
	 * @return mixed
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function callFunction(string $name, ...$params)
	{
		$bindings = [];
		$list = [];

		foreach ($params as $index => $param)
		{
			$bindings[] = $this->guessValue($param);
			$list[] = ':param' . $index;
		}

		$statement = $this->prepare(sprintf('SELECT %s(%s) AS result', $name, implode(',', $list)));

		foreach ($bindings as $index => $binding)
			$statement->bind(':param' . $index, $binding[0], $binding[1]);

		return $statement->execute()->toSingle('result');
	}

	/**
	 * Executes a stored procedure.
	 *
	 * @param string $name
	 * @param array  $input
	 * @param array  $output
	 *
	 * @note(Bas): Figure out if this can be improved.
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function executeProcedure(string $name, array $input, array &$output): void
	{
		$inputParams = [];
		$outputParams = [];

		$input = $this->guessValues($input);

		for ($i = 0, $length = count($input); $i < $length; $i++)
			$inputParams[] = ':in' . $i;

		for ($i = 0, $length = count($output); $i < $length; $i++)
			$outputParams[] = '@out' . $i;

		$parameterList = implode(', ', array_merge($inputParams, $outputParams));

		try
		{
			$smt = $this->pdo->prepare(sprintf('CALL %s(%s)', $name, $parameterList));

			foreach ($input as $index => $param)
				$smt->bindValue(':in' . $index, $param[0], $param[1]);

			$smt->execute();

			$smt = $this->pdo->prepare(sprintf('SELECT %s;', implode(', ', $outputParams)));
			$smt->execute();

			$result = $smt->fetch();

			for ($i = 0, $length = count($output); $i < $length; $i++)
				$output[array_keys($output)[$i]] = $result[$i] ?? null;
		}
		catch (PDOException $err)
		{
			throw new DatabaseException('Execution of procedure failed.', DatabaseException::ERR_PROCEDURE_FAILED, $err);
		}
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

	/**
	 * Exposes our PDO instance.
	 *
	 * @param callable $fn
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function withPDO(callable $fn): void
	{
		$fn($this->pdo);
	}

}
