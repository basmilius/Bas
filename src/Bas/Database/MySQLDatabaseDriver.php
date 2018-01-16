<?php
declare(strict_types=1);

namespace Bas\Database;

/**
 * Class MySQLDatabaseDriver
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Database
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
	 * @param array  $options
	 * @param bool   $connectAutomatically
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct (string $database, string $host, int $port = 3306, $username = '', $password = '', array $options = [], $connectAutomatically = true)
	{
		$dsn = "mysql:dbname={$database};host={$host};port={$port}";

		parent::__construct($dsn, $username, $password, $options, $connectAutomatically);
	}

}