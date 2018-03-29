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
 * Class Transaction
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database
 * @since 1.0.0
 */
final class Transaction extends AbstractDatabaseDriver
{

	/**
	 * Transaction constructor.
	 *
	 * @param DatabaseDriver $driver
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct(DatabaseDriver $driver)
	{
		parent::__construct($driver);

		$driver->pdo()->beginTransaction();
	}

	/**
	 * Begins the {@see Transaction}.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function begin(): bool
	{
		return $this->driver->pdo()->beginTransaction();
	}

	/**
	 * Commits the {@see Transaction}.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function commit(): bool
	{
		return $this->driver->pdo()->commit();
	}

	/**
	 * Rolls the {@see Transaction} back.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function rollBack(): bool
	{
		return $this->driver->pdo()->rollBack();
	}

}
