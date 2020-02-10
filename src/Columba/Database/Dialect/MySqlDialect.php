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

namespace Columba\Database\Dialect;

use Columba\Database\Query\Builder\Builder;

/**
 * Class MySqlDialect
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Dialect
 * @since 1.6.0
 */
class MySqlDialect extends Dialect
{

	public array $escapers = ['`', '`'];

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function foundRows(Builder $query): Builder
	{
		return $query->select(['FOUND_ROWS()']);
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function optimizeTable(Builder $query, array $tables): Builder
	{
		return $query
			->addPiece('OPTIMIZE TABLE', $tables, 0, 1, 1, $this->columnSeparator);
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function tableExists(Builder $query, string $schema, string $table): Builder
	{
		return $query
			->select(['table_name'])
			->from('information_schema.tables')
			->where('table_schema', $schema)
			->and('table_name', $table)
			->and('table_type', 'BASE TABLE');
	}

}
