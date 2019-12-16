<?php
/**
 * Copyright (c) 2017 - 2019 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Latte Framework package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Database\Connector;

use Columba\Database\Error\DatabaseException;
use Columba\Database\Util\ErrorUtil;
use PDO;
use PDOException;

/**
 * Class Connector
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Connector
 * @since 1.6.0
 */
abstract class Connector
{

	private const DEFAULT_OPTIONS = [
		PDO::ATTR_CASE => PDO::CASE_NATURAL,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_EMULATE_PREPARES => false
	];

	private string $dsn;
	private string $database;
	private ?string $username;
	private ?string $password;
	private array $options;

	/**
	 * Connector constructor.
	 *
	 * @param string      $dsn
	 * @param string      $database
	 * @param string|null $username
	 * @param string|null $password
	 * @param array       $options
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(string $dsn, string $database, ?string $username = null, ?string $password = null, array $options = [])
	{
		$this->dsn = $dsn;
		$this->database = $database;
		$this->username = $username;
		$this->password = $password;

		foreach (self::DEFAULT_OPTIONS as $key => $value)
			$this->options[$key] = $value;

		foreach ($options as $key => $value)
			$this->options[$key] = $value;
	}

	/**
	 * Creates a {@see PDO} instance.
	 *
	 * @return PDO
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function createPdoInstance(): PDO
	{
		try
		{
			return new PDO($this->dsn, $this->username, $this->password, $this->options);
		}
		catch (PDOException $err)
		{
			throw ErrorUtil::throw($err->getCode(), $err->getMessage(), $err);
		}
	}

	/**
	 * Gets the DSN string.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getDsn(): string
	{
		return $this->dsn;
	}

	/**
	 * Gets the database name.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getDatabase(): string
	{
		return $this->database;
	}

	/**
	 * Gets the username.
	 *
	 * @return string|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getUsername(): ?string
	{
		return $this->username;
	}

	/**
	 * Gets the password.
	 *
	 * @return string|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getPassword(): ?string
	{
		return $this->password;
	}

	/**
	 * Gets the options.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getOptions(): array
	{
		return $this->options;
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function __debugInfo(): ?array
	{
		return null;
	}

}
