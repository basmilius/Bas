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

/**
 * Class MySQLDatabaseDriver
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database
 * @since 1.0.0
 */
final class MySQLDatabaseDriver extends DatabaseDriver
{

	/**
	 * MySQLDatabaseDriver constructor.
	 *
	 * @param string $database
	 * @param string $host
	 * @param int    $port
	 * @param string $username
	 * @param string $password
	 * @param string $charset
	 * @param array  $options
	 * @param bool   $connectAutomatically
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct(string $database, string $host, int $port = 3306, $username = '', $password = '', string $charset = 'utf8mb4', array $options = [], $connectAutomatically = true)
	{
		$dsn = "mysql:dbname=$database;host=$host;port=$port;charset=$charset";

		parent::__construct($dsn, $username, $password, $options, $connectAutomatically);
	}

}
