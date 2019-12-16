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

use Columba\Foundation\Preferences\Preferences;
use Columba\Database\Error\ConnectionException;

/**
 * Class MySqlConnector
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Connector
 * @since 1.6.0
 */
class MySqlConnector extends Connector
{

	/**
	 * MySqlConnector constructor.
	 *
	 * @param string      $host
	 * @param string      $database
	 * @param string|null $username
	 * @param string|null $password
	 * @param int         $port
	 * @param array       $options
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(string $host, string $database, ?string $username = null, ?string $password = null, int $port = 3306, array $options = [])
	{
		$dsn = "mysql:host={$host};port={$port};dbname={$database}";

		parent::__construct($dsn, $database, $username, $password, $options);
	}

	/**
	 * Creates a {@see MySqlConnector} instance from the given options.
	 *
	 * @param array $options
	 *
	 * @return self
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function fromOptions(array $options): self
	{
		if (!isset($options['host']))
			throw new ConnectionException('Missing the required host option.', ConnectionException::ERR_INCOMPLETE_OPTIONS);

		if (!isset($options['database']))
			throw new ConnectionException('Missing the required database option.', ConnectionException::ERR_INCOMPLETE_OPTIONS);

		$username = $options['username'] ?? null;
		$password = $options['password'] ?? null;
		$port = $options['port'] ?? 3306;

		return new static($options['host'], $options['database'], $username, $password, $port, $options['options'] ?? []);
	}

	/**
	 * Creates a {@see MySqlConnector} instance from the given {@see Preferences} instance.
	 *
	 * @param Preferences $preferences
	 *
	 * @return self
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function fromPreferences(Preferences $preferences): self
	{
		if (!isset($preferences['host']))
			throw new ConnectionException('Missing the required host option.', ConnectionException::ERR_INCOMPLETE_OPTIONS);

		if (!isset($preferences['database']))
			throw new ConnectionException('Missing the required database option.', ConnectionException::ERR_INCOMPLETE_OPTIONS);

		$username = $preferences['username'] ?? null;
		$password = $preferences['password'] ?? null;
		$port = $preferences['port'] ?? 3306;
		$options = $preferences['options'] ?? [];

		return new static($preferences['host'], $preferences['database'], $username, $password, $port, $options);
	}

}
