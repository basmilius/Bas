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

/**
 * Class Transaction
 *
 * @package Columba\Database
 * @author Bas Milius <bas@mili.us>
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

		$driver->pdo->beginTransaction();
	}

	/**
	 * Gets the parent driver.
	 *
	 * @return DatabaseDriver
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function getDriver(): AbstractDatabaseDriver
	{
		return $this->driver;
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
		return $this->driver->pdo->commit();
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
		return $this->driver->pdo->rollBack();
	}

}
