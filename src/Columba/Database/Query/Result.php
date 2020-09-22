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

namespace Columba\Database\Query;

use Columba\Database\Connection\Connection;
use Columba\Facade\IsCountable;
use PDO;

/**
 * Class Result
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Query
 * @since 1.6.0
 */
class Result implements IsCountable
{

	protected Connection $connection;
	protected Statement $statement;
	protected bool $allowModel;

	/**
	 * Result constructor.
	 *
	 * @param Connection $connection
	 * @param Statement $statement
	 *
	 * @param bool $allowModel
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(Connection $connection, Statement $statement, bool $allowModel = true)
	{
		$this->connection = $connection;
		$this->statement = $statement;
		$this->allowModel = $allowModel;
	}

	/**
	 * Fetches the next element.
	 *
	 * @param int $fetchMode
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function yield(int $fetchMode = PDO::FETCH_ASSOC)
	{
		return $this->fetch($fetchMode);
	}

	/**
	 * Fetches a single row.
	 *
	 * @param int $fetchMode
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function fetch(int $fetchMode = PDO::FETCH_ASSOC)
	{
		return $this->statement->fetch($this->allowModel, $fetchMode);
	}

	/**
	 * Fetches all rows.
	 *
	 * @param int $fetchMode
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function fetchAll(int $fetchMode = PDO::FETCH_ASSOC)
	{
		return $this->statement->fetchAll($this->allowModel, $fetchMode);
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function count(): int
	{
		return $this->statement->rowCount();
	}

}
