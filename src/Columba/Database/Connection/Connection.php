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

namespace Columba\Database\Connection;

use Columba\Database\Cache;
use Columba\Database\Connector\Connector;
use Columba\Database\Dialect\Dialect;
use Columba\Database\Error\DatabaseException;
use Columba\Database\Error\QueryException;
use Columba\Database\Query\Builder\Builder;
use Columba\Database\Query\Statement;
use Columba\Database\Util\ErrorUtil;
use PDO;
use function intval;
use function strval;

/**
 * Class Connection
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Connection
 * @since 1.6.0
 */
abstract class Connection
{

	private Cache $cache;
	private Connector $connector;
	private Dialect $dialect;
	private ?PDO $pdo = null;

	/**
	 * Connection constructor.
	 *
	 * @param Connector $connector
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(Connector $connector)
	{
		$this->cache = new Cache();
		$this->connector = $connector;
		$this->dialect = $this->createDialectInstance();
	}

	/**
	 * Creates the {@see Dialect} instance.
	 *
	 * @return Dialect
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function createDialectInstance(): Dialect
	{
		return new Dialect();
	}

	/**
	 * Connects to the database.
	 *
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function connect(): void
	{
		$this->pdo = $this->connector->createPdoInstance();
	}

	/**
	 * Disconnects from the database.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function disconnect(): void
	{
		$this->pdo = null;
	}

	/**
	 * Gets a connection attribute.
	 *
	 * @param int $attribute
	 *
	 * @return mixed
	 * @see PDO::getAttribute()
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function attribute(int $attribute)
	{
		return $this->pdo->getAttribute($attribute);
	}

	/**
	 * Executes the given query and returns the amount of affected rows.
	 *
	 * @param string $query
	 *
	 * @return int
	 * @throws DatabaseException
	 * @see PDO::exec()
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function execute(string $query): int
	{
		$result = $this->pdo->exec($query);

		if ($result !== false)
			return $result;

		throw $this->throwFromErrorInfo();
	}

	/**
	 * Gets a connection attribute.
	 *
	 * @return int
	 * @see PDO::getAttribute()
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function foundRows(): int
	{
		return $this->queryColumn($this->dialect->foundRows($this->query())->build());
	}

	/**
	 * Gets the last insert id as string.
	 *
	 * @param string|null $name
	 *
	 * @return string
	 * @see PDO::lastInsertId()
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function lastInsertId(?string $name = null): string
	{
		return $this->pdo->lastInsertId($name);
	}

	/**
	 * Gets the last insert id as integer.
	 *
	 * @param string|null $name
	 *
	 * @return int
	 * @see PDO::lastInsertId()
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function lastInsertIdInteger(?string $name = null): int
	{
		return intval($this->pdo->lastInsertId($name));
	}

	/**
	 * Initiates a prepared {@see Statement}.
	 *
	 * @param string $query
	 * @param array  $options
	 *
	 * @return Statement
	 * @throws DatabaseException
	 * @see Statement
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function prepare(string $query, array $options = []): Statement
	{
		return new Statement($this, $query, $options);
	}

	/**
	 * Creates a query {@see Builder} instance.
	 *
	 * @return Builder
	 * @see Builder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function query(): Builder
	{
		return new Builder($this);
	}

	/**
	 * Executes the given query and returns the first column.
	 *
	 * @param string $query
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function queryColumn(string $query)
	{
		return $this->pdo->query($query)->fetchColumn();
	}

	/**
	 * Quotes the given value.
	 *
	 * @param mixed $value
	 * @param int   $type
	 *
	 * @return string
	 * @see PDO::quote()
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function quote($value, int $type = PDO::PARAM_STR): string
	{
		return $this->pdo->quote(strval($value), $type);
	}

	/**
	 * Checks if the given table exists in the current database.
	 *
	 * @param string      $table
	 * @param string|null $database
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function tableExists(string $table, ?string $database = null): bool
	{
		$database ??= $this->connector->getDatabase();

		$statement = $this->dialect
			->tableExists(self::query(), $database, $table)
			->statement();

		$statement->run();

		return $statement->rowCount() > 0;
	}

	/**
	 * Adds wildcards to the given value.
	 *
	 * @param string $value
	 * @param bool   $left
	 * @param bool   $right
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function wildcard(string $value, bool $left = true, bool $right = true): array
	{
		$str = '';

		if ($left) $str .= '%';
		$str .= $value;
		if ($right) $str .= '%';

		return [$str, PDO::PARAM_STR];
	}

	/**
	 * Commits the active transaction.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function commit(): bool
	{
		if (!$this->pdo->inTransaction())
			throw new QueryException('There is no active transaction.', QueryException::ERR_NO_TRANSACTION);

		return $this->pdo->commit();
	}

	/**
	 * Returns TRUE if there is an active transaction.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function inTransaction(): bool
	{
		return $this->pdo->inTransaction();
	}

	/**
	 * Rolls the transaction back.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function rollBack(): bool
	{
		if (!$this->pdo->inTransaction())
			throw new QueryException('There is no active transaction.', QueryException::ERR_NO_TRANSACTION);

		return $this->pdo->rollBack();
	}

	/**
	 * Begins a transaction.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function transaction(): bool
	{
		return $this->pdo->beginTransaction();
	}

	/**
	 * Gets the used {@see Cache} instance.
	 *
	 * @return Cache
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function getCache(): Cache
	{
		return $this->cache;
	}

	/**
	 * Gets the used {@see Connector} instance.
	 *
	 * @return Connector
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function getConnector(): Connector
	{
		return $this->connector;
	}

	/**
	 * Gets the used {@see Dialect} instance.
	 *
	 * @return Dialect
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function getDialect(): Dialect
	{
		return $this->dialect;
	}

	/**
	 * Gets the {@see PDO} instance.
	 *
	 * @return PDO|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function getPdo(): ?PDO
	{
		return $this->pdo;
	}

	/**
	 * Throws an error from {@see PDO::errorInfo()}.
	 *
	 * @return DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private function throwFromErrorInfo(): DatabaseException
	{
		[, $code, $message] = $this->pdo->errorInfo();

		return ErrorUtil::throw($code, $message);
	}

}
