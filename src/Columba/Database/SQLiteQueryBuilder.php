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
 * Class SQLiteQueryBuilder
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database
 * @since 1.5.0
 */
class SQLiteQueryBuilder extends QueryBuilder
{

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function optimizeTable(string ...$table): QueryBuilder
	{
		$this->add('VACUUM', $this->escapeFields($table), 0, 1, 1, self::DEFAULT_FIELD_SEPARATOR);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function fullJoin(string $table): QueryBuilder
	{
		return $this->unsupportedFeature(__METHOD__);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function rightJoin(string $table): QueryBuilder
	{
		return $this->unsupportedFeature(__METHOD__);
	}

}
