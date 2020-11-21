<?php
/**
 * Copyright (c) 2019 - 2020 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Database;

use Columba\Database\Connection\Connection;
use Columba\Database\Connector\Connector;
use Columba\Database\Error\ConnectionException;
use Columba\Database\Query\Builder\Builder;
use Columba\Database\Query\Statement;
use PDO;
use function is_subclass_of;
use function sprintf;

/**
 * Class Db
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database
 * @since 1.6.0
 */
class Db
{

	public static bool $enableModelDebugInformation = false;
	public static bool $enableQueryTracking = false;
	public static array $trackedQueries = [];

	/** @var Connection[] */
	private static array $connections = [];

	protected static string $defaultConnectionId = 'default';

	private static array $connected = [];

	/**
	 * Creates a connection instance.
	 *
	 * @param string $connectionClass
	 * @param Connector $connector
	 * @param string $id
	 * @param bool $connect
	 *
	 * @return Connection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function create(string $connectionClass, Connector $connector, string $id = 'default', bool $connect = true): Connection
	{
		if (!is_subclass_of($connectionClass, Connection::class))
			throw new ConnectionException('The given class is not a subclass of ' . Connection::class, ConnectionException::ERR_INVALID_CONNECTION);

		/** @var Connection $connection */
		$connection = new $connectionClass($connector, $id);

		if ($connect)
		{
			static::$connected[$id] = true;
			$connection->connect();
		}

		self::register($connection, $id);

		return $connection;
	}

	/**
	 * Gets a connection by the given id, or the default one.
	 *
	 * @param string|null $id
	 *
	 * @return Connection|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function get(?string $id = null): ?Connection
	{
		$id ??= static::$defaultConnectionId;
		$connection = self::$connections[$id] ?? null;

		if ($connection === null)
			return null;

		if (!isset(static::$connected[$id]) && $connection->getPdo() === null)
		{
			static::$connected[$id] = true;
			$connection->connect();
		}

		return $connection;
	}

	/**
	 * Gets a connection by the given id, or the default one.
	 *
	 * @param string|null $id
	 *
	 * @return Connection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function getOrFail(?string $id = null): Connection
	{
		$id ??= static::$defaultConnectionId;

		if (!isset(self::$connections[$id]))
			throw new ConnectionException(sprintf('Database connection with id %s not found.', $id), ConnectionException::ERR_UNDEFINED_CONNECTION);

		return static::get($id);
	}

	/**
	 * Registers a connection.
	 *
	 * @param Connection $connection
	 * @param string|null $id
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function register(Connection $connection, ?string $id = null): void
	{
		self::$connections[$id ?? static::$defaultConnectionId] = $connection;
	}

	/**
	 * Removes a connection.
	 *
	 * @param string $id
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function unregister(string $id): void
	{
		unset(self::$connections[$id]);
	}

	/**
	 * @param int $attribute
	 * @param string|null $id
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @see Connection::attribute()
	 */
	public static function attribute(int $attribute, ?string $id = null)
	{
		return self::getOrFail($id)->attribute($attribute);
	}

	/**
	 * @param string $query
	 * @param string|null $id
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @see Connection::execute()
	 */
	public static function execute(string $query, ?string $id = null): int
	{
		return self::getOrFail($id)->execute($query);
	}

	/**
	 * @param string|null $id
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @see Connection::foundRows()
	 */
	public static function foundRows(?string $id = null): int
	{
		return self::getOrFail($id)->foundRows();
	}

	/**
	 * @param string|null $name
	 * @param string|null $id
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @see Connection::lastInsertId()
	 */
	public static function lastInsertId(?string $name = null, ?string $id = null): string
	{
		return self::getOrFail($id)->lastInsertId($name);
	}

	/**
	 * @param string|null $name
	 * @param string|null $id
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @see Connection::lastInsertIdInteger()
	 */
	public static function lastInsertIdInteger(?string $name = null, ?string $id = null): int
	{
		return (int)self::getOrFail($id)->lastInsertId($name);
	}

	/**
	 * @param string $query
	 * @param array $options
	 * @param string|null $id
	 *
	 * @return Statement
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @see Connection::prepare()
	 */
	public static function prepare(string $query, array $options = [], ?string $id = null): Statement
	{
		return self::getOrFail($id)->prepare($query, $options);
	}

	/**
	 * @param string|null $id
	 *
	 * @return Builder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @see Connection::query()
	 */
	public static function query(?string $id = null): Builder
	{
		return self::getOrFail($id)->query();
	}

	/**
	 * @param Builder|string $query
	 * @param string|null $id
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @see Connection::queryColumn()
	 */
	public static function queryColumn($query, ?string $id = null)
	{
		return self::getOrFail($id)->queryColumn($query);
	}

	/**
	 * @param mixed $value
	 * @param int $type
	 * @param string|null $id
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @see Connection::quote()
	 */
	public static function quote($value, int $type = PDO::PARAM_STR, ?string $id = null): string
	{
		return self::getOrFail($id)->quote($value, $type);
	}

	/**
	 * @param string $table
	 * @param string|null $id
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @see Connection::tableExists()
	 */
	public static function tableExists(string $table, ?string $id = null): bool
	{
		return self::getOrFail($id)
			->tableExists($table);
	}

	/**
	 * @param string $value
	 * @param bool $left
	 * @param bool $right
	 * @param string|null $id
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @see Connection::wildcard()
	 */
	public static function wildcard(string $value, bool $left = true, bool $right = true, ?string $id = null): array
	{
		return self::getOrFail($id)->wildcard($value, $left, $right);
	}

	/**
	 * @param string|null $id
	 *
	 * @return Cache
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @see Connection::getCache()
	 */
	public static function cache(?string $id = null): Cache
	{
		return self::getOrFail($id)->getCache();
	}

	/**
	 * @param string|null $id
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @see Connection::commit()
	 */
	public static function commit(?string $id = null): bool
	{
		return self::getOrFail($id)->commit();
	}

	/**
	 * @param string|null $id
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @see Connection::inTransaction()
	 */
	public static function inTransaction(?string $id = null): bool
	{
		return self::getOrFail($id)->inTransaction();
	}

	/**
	 * @param string|null $id
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @see Connection::rollBack()
	 */
	public static function rollBack(?string $id = null): bool
	{
		return self::getOrFail($id)->rollBack();
	}

	/**
	 * @param string|null $id
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @see Connection::transaction()
	 */
	public static function transaction(?string $id = null): bool
	{
		return self::getOrFail($id)->transaction();
	}

}
