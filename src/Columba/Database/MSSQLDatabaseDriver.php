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

/**
 * Class MSSQLDatabaseDriver
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database
 * @since 1.5.0
 */
class MSSQLDatabaseDriver extends DatabaseDriver
{

	public const DRIVER_DBLIB = 1;
	public const DRIVER_SQLSRV = 2;

	/**
	 * MSSQLDatabaseDriver constructor.
	 *
	 * @param string $host
	 * @param string $database
	 * @param string $username
	 * @param string $password
	 * @param int    $driver
	 * @param array  $options
	 * @param bool   $connectAutomatically
	 *
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function __construct(string $host, string $database, string $username, string $password, int $driver = self::DRIVER_DBLIB, array $options = [], bool $connectAutomatically = true)
	{
		if ($driver === self::DRIVER_DBLIB)
			$dsn = "dblib:version=8.0;charset=UTF-8;host=$host;dbname=$database;";
		else if ($driver === self::DRIVER_SQLSRV)
			$dsn = "sqlsrv:Server=$host;Database=$database";
		else
			throw new DatabaseException('Unknown SQL Server driver', DatabaseException::ERR_UNSUPPORTED);

		parent::__construct($dsn, $username, $password, $options, $connectAutomatically);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function createQueryBuilder(): QueryBuilder
	{
		return new MSSQLQueryBuilder($this);
	}

}
