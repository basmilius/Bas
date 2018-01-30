<?php
/**
 * This file is part of the Bas package.
 *
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bas\Database;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Class PreparedStatement
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Database
 * @since 1.0.0
 */
final class PreparedStatement
{

	/**
	 * @var AbstractDatabaseDriver
	 */
	private $driver;

	/**
	 * @var PDOStatement
	 */
	private $statement;

	/**
	 * PreparedStatement constructor.
	 *
	 * @param AbstractDatabaseDriver $driver
	 * @param PDOStatement           $statement
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct (AbstractDatabaseDriver $driver, PDOStatement $statement)
	{
		$this->driver = $driver;
		$this->statement = $statement;
	}

	/**
	 * Binds a value as a named parameter in the statement.
	 *
	 * @param string $param
	 * @param mixed  $value
	 * @param int    $paramType
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function bind (string $param, $value, int $paramType): void
	{
		$this->statement->bindValue($param, $value, $paramType);
	}

	/**
	 * Binds a boolean value as a named parameter in the statement.
	 *
	 * @param string $param
	 * @param bool   $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function bindBool (string $param, bool $value): void
	{
		$this->bind($param, $value ? 1 : 0, PDO::PARAM_INT);
	}

	/**
	 * Binds a float value as a named parameter in the statement.
	 *
	 * @param string $param
	 * @param float  $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function bindFloat (string $param, float $value): void
	{
		$this->bind($param, $value, PDO::PARAM_STR);
	}

	/**
	 * Binds an integer value as a named parameter in the statement.
	 *
	 * @param string   $param
	 * @param int|null $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function bindInt (string $param, ?int $value): void
	{
		$this->bind($param, $value, $value !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
	}

	/**
	 * Binds a string value as a named parameter in the statement.
	 *
	 * @param string      $param
	 * @param string|null $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function bindString (string $param, ?string $value): void
	{
		$this->bind($param, $value, $value !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
	}

	/**
	 * Debug Dump Parameters.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function debugDumpParams (): void
	{
		$this->statement->debugDumpParams();
	}

	/**
	 * Executes the statement.
	 *
	 * @return ResultSet
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function execute (): ResultSet
	{
		$result = $this->statement->execute();

		if ($result)
			return new ResultSet($this, $this->statement);

		throw new PDOException(strval($this->statement->errorInfo()[2]), intval($this->statement->errorCode()));
	}

}
