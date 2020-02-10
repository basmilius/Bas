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

use Columba\Facade\ICountable;
use Columba\Database\Connection\Connection;

/**
 * Class Result
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Query
 * @since 1.6.0
 */
class Result implements ICountable
{

	protected Connection $connection;
	protected Statement $statement;
	protected bool $allowModel;

	/**
	 * Result constructor.
	 *
	 * @param Connection $connection
	 * @param Statement  $statement
	 *
	 * @param bool       $allowModel
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
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function yield()
	{
		return $this->fetch();
	}

	/**
	 * Fetches a single row.
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function fetch()
	{
		return $this->statement->fetch($this->allowModel);
	}

	/**
	 * Fetches all rows.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function fetchAll()
	{
		return $this->statement->fetchAll($this->allowModel);
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
