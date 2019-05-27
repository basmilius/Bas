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

/**
 * Class DatabaseDriver
 *
 * @package Columba\Database
 * @author Bas Milius <bas@mili.us>
 * @since 1.0.0
 */
abstract class DatabaseDriver extends AbstractDatabaseDriver
{

	/**
	 * @var string
	 */
	private $dsn;

	/**
	 * @var string
	 */
	private $username;

	/**
	 * @var string
	 */
	private $password;

	/**
	 * @var array
	 */
	private $options;

	/**
	 * DatabaseDriver constructor.
	 *
	 * @param string $dsn
	 * @param string $username
	 * @param string $password
	 * @param array  $options
	 * @param bool   $connectAutomatically
	 *
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct(string $dsn, string $username = '', string $password = '', array $options = [], bool $connectAutomatically = true)
	{
		parent::__construct($this);

		$this->dsn = $dsn;
		$this->username = $username;
		$this->password = $password;
		$this->options = $options;

		if ($connectAutomatically)
			$this->connect();
	}

	/**
	 * Creates and begins a new {@see Transaction}.
	 *
	 * @return Transaction
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function begin(): Transaction
	{
		return new Transaction($this);
	}

	/**
	 * Connects to the database.
	 *
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function connect(): void
	{
		try
		{
			$pdo = new PDO($this->dsn, $this->username, $this->password, $this->options);
			$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
			$this->pdo = $pdo;
		}
		catch (PDOException $err)
		{
			throw new DatabaseException('Could not connect to database server.', DatabaseException::ERR_CONNECTION_FAILED, $err);
		}
	}

	/**
	 * Closes the connection.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function close(): void
	{
		$this->pdo = null;
	}

	/**
	 * {@inheritdoc}
	 * @note(Bas): Hides everything in this class for print_r and var_dump. Prevents the password from being showed.
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function __debugInfo()
	{
		return null;
	}

}
