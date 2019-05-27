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
 * Class MySQLDatabaseDriver
 *
 * @package Columba\Database
 * @author Bas Milius <bas@mili.us>
 * @since 1.0.0
 */
class MySQLDatabaseDriver extends DatabaseDriver
{

	/**
	 * MySQLDatabaseDriver constructor.
	 *
	 * @param string $host
	 * @param string $database
	 * @param string $username
	 * @param string $password
	 * @param int    $port
	 * @param array  $options
	 * @param bool   $connectAutomatically
	 *
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct(string $host, string $database, string $username = '', string $password = '', int $port = 3306, array $options = [], $connectAutomatically = true)
	{
		$dsn = "mysql:dbname=$database;host=$host;port=$port;charset=utf8mb4";

		parent::__construct($dsn, $username, $password, $options, $connectAutomatically);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function connect(): void
	{
		parent::connect();

		$this->query('SET NAMES utf8')->execute();
	}

}
