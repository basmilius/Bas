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

use Columba\Database\Connector\MySqlConnector;
use Columba\Database\Dialect\Dialect;
use Columba\Database\Dialect\MySqlDialect;
use function Columba\Database\Query\Builder\stringLiteral;

/**
 * Class MySqlConnection
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Connection
 * @since 1.6.0
 */
class MySqlConnection extends Connection
{

	/**
	 * MySqlConnection constructor.
	 *
	 * @param MySqlConnector $connector
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(MySqlConnector $connector)
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
		return new MySqlDialect();
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function loadTablesWithColumns(): void
	{
		$result = $this
			->query()
			->select(['TABLE_NAME', 'COLUMN_NAME'])
			->from('information_schema.COLUMNS')
			->where('TABLE_SCHEMA', stringLiteral($this->getConnector()->getDatabase()))
			->array();

		foreach ($result as ['TABLE_NAME' => $table, 'COLUMN_NAME' => $column])
		{
			if (!isset($this->tablesWithColumns[$table]))
				$this->tablesWithColumns[$table] = [];

			$this->tablesWithColumns[$table][] = $column;
		}
	}

}
