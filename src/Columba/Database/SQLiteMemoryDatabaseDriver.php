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
 * Class SQLiteMemoryDatabaseDriver
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database
 * @since 1.5.0
 */
class SQLiteMemoryDatabaseDriver extends SQLiteDatabaseDriver
{

	/**
	 * SQLiteMemoryDatabaseDriver constructor.
	 *
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function __construct()
	{
		parent::__construct(':memory:');
	}

}
