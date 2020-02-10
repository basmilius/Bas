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

namespace Columba\Database\Connection;

use Columba\Database\Connector\MariaDbConnector;
use Columba\Database\Dialect\Dialect;
use Columba\Database\Dialect\MariaDbDialect;

/**
 * Class MariaDbConnection
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Connection
 * @since 1.6.0
 */
class MariaDbConnection extends MySqlConnection
{

	/**
	 * MySqlConnection constructor.
	 *
	 * @param MariaDbConnector $connector
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(MariaDbConnector $connector)
	{
		parent::__construct($connector);
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function createDialectInstance(): Dialect
	{
		return new MariaDbDialect();
	}

}
