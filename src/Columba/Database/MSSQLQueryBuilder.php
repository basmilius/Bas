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
 * Class MSSQLQueryBuilder
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database
 * @since 1.5.0
 */
class MSSQLQueryBuilder extends QueryBuilder
{

	/**
	 * @var string
	 */
	protected $escapeLeft = '[';

	/**
	 * @var string
	 */
	protected $escapeRight = ']';

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function limit(int $limit, int $offset = 0): QueryBuilder
	{
		if (!$this->has('ORDER BY'))
			throw new DatabaseException('ORDER BY is required to limit the query.', DatabaseException::ERR_UNSUPPORTED);

		$this->add('OFFSET', $offset . ' ROWS', 0, 0, 0);
		$this->add('FETCH NEXT', $limit . ' ROWS ONLY', 0, 0, 0);

		return $this;
	}

}
