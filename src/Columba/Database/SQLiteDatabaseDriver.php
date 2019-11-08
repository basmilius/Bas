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

use function is_readable;

/**
 * Class SQLiteDatabaseDriver
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database
 * @since 1.5.0
 */
class SQLiteDatabaseDriver extends DatabaseDriver
{

	/**
	 * SQLiteDatabaseDriver constructor.
	 *
	 * @param string $fileName
	 * @param bool   $autoCreate
	 *
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function __construct(string $fileName, bool $autoCreate = true)
	{
		if ($fileName !== ':memory:' && !is_readable($fileName) && !$autoCreate)
			throw new DatabaseException('SQLite database file is not readable.', DatabaseException::ERR_FILE_NOT_READABLE);

		parent::__construct('sqlite:' . $fileName);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function createQueryBuilder(): QueryBuilder
	{
		return new SQLiteQueryBuilder($this);
	}

}
