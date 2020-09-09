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

namespace Columba\Database\Query\Builder;

use Columba\Database\Error\QueryException;
use Columba\Util\ArrayUtil;
use PDO;
use function array_keys;
use function array_map;
use function array_values;
use function count;
use function is_array;
use function strpos;

/**
 * Class Builder
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Query\Builder
 * @since 1.6.0
 */
class Builder extends Base
{

	/**
	 * Paginates the result.
	 *
	 * @param int $limit
	 * @param callable|null $withCollection
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function paginate(int $limit = 20, ?callable $withCollection = null): array
	{
		$page = max(1, intval($_GET['page'] ?? '1'));
		$offset = ($page - 1) * $limit;

		if ($this->pieces[0][0] === 'SELECT')
			$this->pieces[0][0] = 'SELECT SQL_CALC_FOUND_ROWS';

		$this->limit($limit, $offset);

		$result = $this->collection([], PDO::FETCH_ASSOC, $foundRows);

		if ($withCollection !== null)
			$result = $result->map($withCollection);

		return [
			'offset' => $offset,
			'limit' => $limit,
			'data' => $result,
			'total' => $foundRows
		];
	}

	/**
	 * Adds an AND clause.
	 *
	 * @param mixed $column
	 * @param mixed $comparator
	 * @param mixed $value
	 * @param bool $addParam
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function and($column = null, $comparator = null, $value = null, bool $addParam = true): self
	{
		return $this->addExpression('AND', $column, $comparator, $value, $addParam);
	}

	/**
	 * Adds a AND $column IS NOT NULL clause.
	 *
	 * @param string $column
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function andNotNull(string $column): self
	{
		return $this->and($column, isNotNull());
	}

	/**
	 * Adds a AND $column IS NULL clause.
	 *
	 * @param string $column
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function andNull(string $column): self
	{
		return $this->and($column, isNull());
	}

	/**
	 * Adds a DELETE clause.
	 *
	 * @param string $table
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function delete(string $table): self
	{
		return $this->addPiece('DELETE', $this->dialect->escapeTable($table), 0, 1, 1);
	}

	/**
	 * Adds a DELETE FROM clause.
	 *
	 * @param string $table
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function deleteFrom(string $table): self
	{
		return $this->addPiece('DELETE FROM', $this->dialect->escapeTable($table), 0, 1, 1);
	}

	/**
	 * Adds a FROM clause.
	 *
	 * @param mixed $tables
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function from(string ...$tables): self
	{
		$tables = array_map(fn(string $table) => $this->dialect->escapeTable($table), $tables);

		return $this->addPiece('FROM', $tables, 0, 1, 1, $this->dialect->columnSeparator);
	}

	/**
	 * Adds a GROUP BY clause.
	 *
	 * @param mixed $columns
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function groupBy(string ...$columns): self
	{
		$columns = array_map(fn(string $column): string => $this->dialect->escapeColumn($column), $columns);

		return $this->addPiece('GROUP BY', $columns, 0, 1, 1, $this->dialect->columnSeparator);
	}

	/**
	 * Adds a HAVING clause.
	 *
	 * @param mixed $column
	 * @param mixed $comparator
	 * @param mixed $value
	 * @param bool $addParam
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function having($column = null, $comparator = null, $value = null, bool $addParam = true): self
	{
		return $this->addExpression('HAVING', $column, $comparator, $value, $addParam);
	}

	/**
	 * Adds a LIMIT ... OFFSET ... clause.
	 *
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function limit(int $limit, int $offset = 0): self
	{
		$this->addPiece('LIMIT', $limit, 0, 1, 1);

		if ($offset > 0)
			$this->offset($offset);

		return $this;
	}

	/**
	 * Adds an OFFSET clause.
	 *
	 * @param int $offset
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function offset(int $offset): self
	{
		return $this->addPiece('OFFSET', $offset, 0, 1, 1);
	}

	/**
	 * Adds an ON clause.
	 *
	 * @param string $left
	 * @param string $comparator
	 * @param string $right
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function on(string $left, string $comparator, string $right): self
	{
		return $this->addPiece('ON', $this->dialect->escapeColumn($left) . ' ' . $comparator . ' ' . $this->dialect->escapeColumn($right), 1);
	}

	/**
	 * Adds an ON DUPLICATE KEY UPDATE clause.
	 *
	 * @param mixed $columns
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function onDuplicateKeyUpdate(string ...$columns): self
	{
		$columns = array_map(fn(string $column) => strpos($column, '=') !== false ? $column : $column . ' = VALUES(' . $column . ')', $columns);

		return $this->addPiece('ON DUPLICATE KEY UPDATE', $columns, 0, 1, 1, $this->dialect->columnSeparator);
	}

	/**
	 * Adds an OPTIMIZE TABLE clause.
	 *
	 * @param mixed $tables
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function optimizeTable(string ...$tables): self
	{
		$tables = array_map(fn(string $table) => $this->dialect->escapeTable($table), $tables);

		return $this->dialect->optimizeTable($this, $tables);
	}

	/**
	 * Adds an OR clause.
	 *
	 * @param mixed $column
	 * @param mixed $comparator
	 * @param mixed $value
	 * @param bool $addParam
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function or($column = null, $comparator = null, $value = null, bool $addParam = true): self
	{
		return $this->addExpression('OR', $column, $comparator, $value, $addParam);
	}

	/**
	 * Adds a OR $column IS NOT NULL clause.
	 *
	 * @param string $column
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function orNotNull(string $column): self
	{
		return $this->or($column, isNotNull());
	}

	/**
	 * Adds a OR $column IS NULL clause.
	 *
	 * @param string $column
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function orNull(string $column): self
	{
		return $this->or($column, isNull());
	}

	/**
	 * Adds a ORDER BY clause.
	 *
	 * @param mixed $orders
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function orderBy(...$orders): self
	{
		$orders = array_map(function ($column): string
		{
			if ($column instanceof Literal)
				return $column->value($this);

			if (strpos($column, ' ASC'))
				return $this->dialect->escapeColumn(substr($column, 0, -4)) . ' ASC';

			if (strpos($column, ' DESC'))
				return $this->dialect->escapeColumn(substr($column, 0, -5)) . ' DESC';

			return $this->dialect->escapeColumn($column);
		}, $orders);

		return $this->addPiece('ORDER BY', $orders, 0, 1, 1, $this->dialect->columnSeparator);
	}

	/**
	 * Adds a ORDER BY $column ASC clause.
	 *
	 * @param string $column
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function orderByAsc(string $column): self
	{
		$column = $this->dialect->escapeColumn($column);

		return $this->addPiece('ORDER BY', asc($column)->value($this), 0, 1, 1, $this->dialect->columnSeparator);
	}

	/**
	 * Adds a ORDER BY $column DESC clause.
	 *
	 * @param string $column
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function orderByDesc(string $column): self
	{
		$column = $this->dialect->escapeColumn($column);

		return $this->addPiece('ORDER BY', desc($column)->value($this), 0, 1, 1, $this->dialect->columnSeparator);
	}

	/**
	 * Adds a SET clause.
	 *
	 * @param string $column
	 * @param mixed $value
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function set(string $column, $value): self
	{
		$value = $this->addParam($value);

		if ($column === null)
			$expression = '';
		else
			$expression = $this->dialect->escapeColumn($column) . ' = ' . $value;

		if ($this->lastClause === 'SET')
		{
			$index = count($this->pieces) - 1;
			$existingStatement = $this->pieces[$index][1];

			if (!is_array($existingStatement))
				$existingStatement = [$existingStatement];

			$existingStatement[] = $expression;

			$this->pieces[$index][1] = $existingStatement;
		}
		else
		{
			$this->addPiece('SET', $expression, 0, ($expression === '' ? 0 : 1), ($expression === '' ? 0 : 1), $this->dialect->columnSeparator);
		}

		return $this;
	}

	/**
	 * Adds a TRUNCATE TABLE clause.
	 *
	 * @param string $table
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function truncateTable(string $table): self
	{
		return $this->addPiece('TRUNCATE TABLE', $this->dialect->escapeTable($table), 0, 1, 1);
	}

	/**
	 * Adds a UNION clause that appends the given query.
	 *
	 * @param Builder $query
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function union(self $query): self
	{
		$this->addPiece('UNION', '');

		return $this->merge($query);
	}

	/**
	 * Adds a UNION ALL clause that appends the given query,
	 *
	 * @param Builder $query
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function unionAll(self $query): self
	{
		$this->addPiece('UNION ALL', '');

		return $this->merge($query);
	}

	/**
	 * Adds a UPDATE clause.
	 *
	 * @param string $table
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function update(string $table): self
	{
		return $this->addPiece('UPDATE', $this->dialect->escapeTable($table), 0, 1, 1);
	}

	/**
	 * Adds a UPDATE ... SET ... clause.
	 *
	 * @param string $table
	 * @param array $columnsAndValues
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function updateValues(string $table, array $columnsAndValues): self
	{
		$this->update($table);

		foreach ($columnsAndValues as $column => $value)
			$this->set($column, $value);

		return $this;
	}

	/**
	 * Adds a VALUES clause.
	 *
	 * @param array $values
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function values(array $values): self
	{
		$values = array_map(fn($value) => $this->addParam($value), $values);

		$this->addPiece($this->hasClause('VALUES') ? ',' : 'VALUES', '');
		$this->parenthesis(fn() => $this->addPiece('', $values, 0, 1, 1, $this->dialect->columnSeparator));

		return $this;
	}

	/**
	 * Adds a WHERE clause.
	 *
	 * @param mixed $column
	 * @param mixed $comparator
	 * @param mixed $value
	 * @param bool $addParam
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function where($column = null, $comparator = null, $value = null, bool $addParam = true): self
	{
		return $this->addExpression($this->hasClause('WHERE') ? 'AND' : 'WHERE', $column, $comparator, $value, $addParam);
	}

	/**
	 * Adds a WHERE $column IS NOT NULL clause.
	 *
	 * @param string $column
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function whereNotNull(string $column): self
	{
		return $this->where($column, isNotNull());
	}

	/**
	 * Adds a WHERE $column IS NULL clause.
	 *
	 * @param string $column
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function whereNull(string $column): self
	{
		return $this->where($column, isNull());
	}

	/**
	 * Adds a WITH expression.
	 *
	 * @param string $name
	 * @param Builder $query
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function with(string $name, self $query): self
	{
		return $this->baseWith('WITH', $name, $query);
	}

	/**
	 * Adds a WITH RECURSIVE expression.
	 *
	 * @param string $name
	 * @param Builder $query
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function withRecursive(string $name, self $query): self
	{
		return $this->baseWith('WITH RECURSIVE', $name, $query);
	}

	/**
	 * Adds a INSERT INTO ... clause.
	 *
	 * @param string $table
	 * @param array $columns
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function insertInto(string $table, array $columns): self
	{
		return $this->baseInsert('INSERT INTO', $table, $columns);
	}

	/**
	 * Adds a INSERT IGNORE INTO ... clause.
	 *
	 * @param string $table
	 * @param array $columns
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function insertIntoIgnore(string $table, array $columns): self
	{
		return $this->baseInsert('INSERT IGNORE INTO', $table, $columns);
	}

	/**
	 * Adds a INSERT INTO ... VALUES ... clause.
	 *
	 * @param string $table
	 * @param array $columnsAndValues
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function insertIntoValues(string $table, array $columnsAndValues): self
	{
		[$columns, $values] = $this->convertColumnsAndValues($columnsAndValues);

		$this->baseInsert('INSERT INTO', $table, $columns);

		foreach ($values as $value)
			$this->values($value);

		return $this;
	}

	/**
	 * Adds a INSERT IGNORE INTO ... VALUES ... clause.
	 *
	 * @param string $table
	 * @param array $columnsAndValues
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function insertIntoValuesIgnore(string $table, array $columnsAndValues): self
	{
		[$columns, $values] = $this->convertColumnsAndValues($columnsAndValues);

		$this->baseInsert('INSERT IGNORE INTO', $table, $columns);

		foreach ($values as $value)
			$this->values($value);

		return $this;
	}

	/**
	 * Adds a REPLACE INTO ... clause.
	 *
	 * @param string $table
	 * @param array $columns
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function replaceInto(string $table, array $columns): self
	{
		return $this->baseInsert('REPLACE INTO', $table, $columns);
	}

	/**
	 * Adds a REPLACE INTO ... VALUES ... clause.
	 *
	 * @param string $table
	 * @param array $columnsAndValues
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function replaceIntoValues(string $table, array $columnsAndValues): self
	{
		[$columns, $values] = $this->convertColumnsAndValues($columnsAndValues);

		$this->baseInsert('REPLACE INTO', $table, $columns);

		foreach ($values as $value)
			$this->values($value);

		return $this;
	}

	/**
	 * Adds a SELECT clause.
	 *
	 * @param array $columns
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function select(array $columns = []): self
	{
		return $this->baseSelect('SELECT', $columns);
	}

	/**
	 * Adds a SELECT clause with the DISTINCT suffix.
	 *
	 * @param array $columns
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function selectDistinct(array $columns = []): self
	{
		return $this->selectSuffix('DISTINCT', $columns);
	}

	/**
	 * Adds a SELECT clause with the SQL_CALC_FOUND_ROWS suffix.
	 *
	 * @param array $columns
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function selectFoundRows(array $columns = []): self
	{
		return $this->selectSuffix('SQL_CALC_FOUND_ROWS', $columns);
	}

	/**
	 * Adds a SELECT clause with a suffix.
	 *
	 * @param string $suffix
	 * @param array $columns
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function selectSuffix(string $suffix, array $columns = []): self
	{
		return $this->baseSelect('SELECT ' . $suffix, $columns);
	}

	/**
	 * Adds a FULL JOIN clause.
	 *
	 * @param string $table
	 * @param callable|null $fn
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.60.
	 */
	public function fullJoin(string $table, ?callable $fn = null): self
	{
		return $this->baseJoin('FULL JOIN', $table, $fn);
	}

	/**
	 * Adds an INNER JOIN clause.
	 *
	 * @param string $table
	 * @param callable|null $fn
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function innerJoin(string $table, ?callable $fn = null): self
	{
		return $this->baseJoin('INNER JOIN', $table, $fn);
	}

	/**
	 * Adds a JOIN clause.
	 *
	 * @param string $table
	 * @param callable|null $fn
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function join(string $table, ?callable $fn = null): self
	{
		return $this->baseJoin('JOIN', $table, $fn);
	}

	/**
	 * Adds a LEFT JOIN clause.
	 *
	 * @param string $table
	 * @param callable|null $fn
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function leftJoin(string $table, ?callable $fn = null): self
	{
		return $this->baseJoin('LEFT JOIN', $table, $fn);
	}

	/**
	 * Adds a LEFT OUTER JOIN clause.
	 *
	 * @param string $table
	 * @param callable|null $fn
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function leftOuterJoin(string $table, ?callable $fn = null): self
	{
		return $this->baseJoin('LEFT OUTER JOIN', $table, $fn);
	}

	/**
	 * Adds a RIGHT JOIN clause.
	 *
	 * @param string $table
	 * @param callable|null $fn
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function rightJoin(string $table, ?callable $fn = null): self
	{
		return $this->baseJoin('RIGHT JOIN', $table, $fn);
	}

	/**
	 * Adds a conditional AND clause.
	 *
	 * @param bool $condition
	 * @param mixed $column
	 * @param mixed $comparator
	 * @param mixed $value
	 * @param bool $addParam
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function conditionalAnd(bool $condition, ?string $column = null, $comparator = null, $value = null, bool $addParam = true): self
	{
		if ($condition)
			return $this->and($column, $comparator, $value, $addParam);

		return $this;
	}

	/**
	 * Adds a conditional OR clause.
	 *
	 * @param bool $condition
	 * @param mixed $column
	 * @param mixed $comparator
	 * @param mixed $value
	 * @param bool $addParam
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function conditionalOr(bool $condition, ?string $column = null, $comparator = null, $value = null, bool $addParam = true): self
	{
		if ($condition)
			return $this->or($column, $comparator, $value, $addParam);

		return $this;
	}

	/**
	 * Base function to create INSERT INTO clauses.
	 *
	 * @param string $clause
	 * @param string $table
	 * @param string[] $columns
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private function baseInsert(string $clause, string $table, array $columns): self
	{
		if (count($columns) === 0)
			throw new QueryException('An insert into query requires columns.', QueryException::ERR_MISSING_COLUMNS);

		$columns = array_map(fn(string $column): string => $this->dialect->escapeColumn($column), $columns);

		$this->addPiece($clause, $this->dialect->escapeTable($table), 0, 1, 1);
		$this->parenthesis(fn() => $this->addPiece('', $columns, 0, 1, 1, $this->dialect->columnSeparator), false);

		return $this;
	}

	/**
	 * Base function to create JOIN clauses.
	 *
	 * @param string $clause
	 * @param string $table
	 * @param callable|null $fn
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private function baseJoin(string $clause, string $table, ?callable $fn = null): self
	{
		$this->addPiece($clause, $this->dialect->escapeTable($table), 1, 0, 0, null);

		$this->indent();

		if ($fn !== null)
			$fn($this);

		$this->outdent();

		return $this;
	}

	/**
	 * Base function to create SELECT clauses.
	 *
	 * @param string $clause
	 * @param string[] $columns
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private function baseSelect(string $clause, array $columns = []): self
	{
		if (count($columns) === 0)
		{
			$columns[] = '*';
		}
		else if (ArrayUtil::isAssociativeArray($columns))
		{
			$result = [];

			foreach ($columns as $alias => $expression)
			{
				$alias = $this->dialect->escapeColumn($alias);

				if (is_bool($expression))
				{
					$result[] = $alias;
				}
				else if ($expression instanceof Base)
				{
					$sql = $expression->build();

					$result[] = '(' . $sql . ') AS ' . $alias;
				}
				else if ($expression instanceof IAfterPiece)
				{
					$query = new self($this->connection);

					$expression->after($query);

					$result[] = $query->build() . ' AS ' . $alias;
				}
				else if ($expression instanceof Value)
				{
					$result[] = $expression->value($this) . ' AS ' . $alias;
				}
				else
				{
					$result[] = $this->dialect->escapeColumn($expression) . ' AS ' . $alias;
				}
			}

			$columns = $result;
		}
		else
		{
			$columns = array_map(function ($column): string
			{
				if (is_array($column) && count($column) === 2)
					return $this->dialect->escapeColumn($column[0]) . ' AS ' . $this->dialect->escapeColumn($column[1]);

				if (is_numeric($column))
					return (string)$column;

				if ($column instanceof Value)
					return $column->value($this);

				return $this->dialect->escapeColumn((string)$column);
			}, $columns);
		}

		return $this->addPiece($clause, $columns, 0, 1, 1, $this->dialect->columnSeparator);
	}

	/**
	 * Base function to create WITH clauses.
	 *
	 * @param string $clause
	 * @param string $name
	 * @param Builder $query
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private function baseWith(string $clause, string $name, self $query): self
	{
		if ($this->lastClause !== $clause)
			$this->addPiece($clause, "$name AS", 0, 0, -1);
		else
			$this->addPiece(',', "$name AS");

		$this->parenthesis(fn() => $this->merge($query, 1), false);

		return $this;
	}

	/**
	 * Converts the given columns and values to two individual arrays.
	 *
	 * @param array $columnsAndValues
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private function convertColumnsAndValues(array $columnsAndValues): array
	{
		if (count($columnsAndValues) > 0 && ArrayUtil::isSequentialArray($columnsAndValues))
		{
			$columns = array_keys($columnsAndValues[0]);
			$values = array_map(fn(array $row) => array_values($row), $columnsAndValues);
		}
		else
		{
			$columns = array_keys($columnsAndValues);
			$values = [array_values($columnsAndValues)];
		}

		return [$columns, $values];
	}

}
