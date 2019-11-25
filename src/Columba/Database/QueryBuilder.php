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

use PDO;
use function array_map;
use function array_shift;
use function count;
use function explode;
use function get_class;
use function implode;
use function in_array;
use function is_array;
use function str_replace;
use function stristr;
use function strpos;

/**
 * Class QueryBuilder
 *
 * @package Columba\Database
 * @author Bas Milius <bas@mili.us>
 * @since 1.0.0
 */
class QueryBuilder
{

	protected const DEFAULT_FIELD_SEPARATOR = ", ";
	protected const DEFAULT_INDENT = "    ";

	/**
	 * @var AbstractDatabaseDriver
	 */
	protected $driver;

	/**
	 * @var string
	 */
	protected $escapeLeft = '`';

	/**
	 * @var string
	 */
	protected $escapeRight = '`';

	/**
	 * @var int
	 */
	private $indention = 0;

	/**
	 * @var string|null
	 */
	protected $modelClass = null;

	/**
	 * @var array
	 */
	protected $params = [];

	/**
	 * @var array
	 */
	private $parts;

	/**
	 * @var bool
	 */
	private $pretty = false;

	/**
	 * @var string|null
	 */
	private $previousClause = null;

	/**
	 * QueryBuilder constructor.
	 *
	 * @param AbstractDatabaseDriver|null $driver
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct(?AbstractDatabaseDriver $driver)
	{
		$this->driver = $driver;
		$this->parts = [];
	}

	/**
	 * Adds a query part.
	 *
	 * @param string      $clause
	 * @param             $data
	 * @param int         $indentSelf
	 * @param int         $indent
	 * @param int         $newLine
	 * @param string|null $separator
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	protected final function add(string $clause, $data, int $indentSelf = 0, int $indent = 0, int $newLine = 0, ?string $separator = null): void
	{
		$this->parts[] = [$clause, $data, $indentSelf + $this->indention, $indent, $newLine, $separator];

		if ($clause !== '(' && $clause !== ')')
			$this->previousClause = $clause;
	}

	/**
	 * Adds a value.
	 *
	 * @param $value
	 *
	 * @return array|string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	protected final function addValue($value)
	{
		if (is_array($value) && count($value) === 2)
		{
			if ($value[0] === null)
				$value[1] = PDO::PARAM_NULL;

			if ($value[1] === PDO::PARAM_BOOL)
			{
				$value[0] = $value[0] ? 1 : 0;
				$value[1] = PDO::PARAM_INT;
			}

			$name = 'param' . (count($this->params) + 1);
			$this->params[] = [$name, $value[0], $value[1]];
			$value = ':' . $name;
		}

		return $value;
	}

	/**
	 * Escapes a field.
	 *
	 * @param string $field
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function escapeField(string $field): string
	{
		if (strpos($field, ' ') || strpos($field, '(') || strpos($field, ')'))
			return $field; // To hard to handle.

		$ignore = ['1', '*'];

		$parts = explode('.', $field);
		$parts = array_map(function (string $field) use ($ignore): string
		{
			if (in_array($field, $ignore))
				return $field;

			if ($field[0] === $this->escapeLeft)
				return $field;

			return $this->escapeLeft . $field . $this->escapeRight;
		}, $parts);

		return implode('.', $parts);
	}

	/**
	 * Escapes multiple fields.
	 *
	 * @param string[] $fields
	 *
	 * @return string[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function escapeFields(array $fields): array
	{
		return array_map([$this, 'escapeField'], $fields);
	}

	/**
	 * Executes the query.
	 *
	 * @return ResultSet
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function execute(): ResultSet
	{
		$smt = $this->driver->prepare($this->__toString());

		foreach ($this->params as [$name, $value, $type])
			$smt->bind($name, $value, $type);

		return $smt->execute($this->modelClass);
	}

	/**
	 * Returns TRUE if {@see $clause} is defined in the query.
	 *
	 * @param string $clause
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function has(string $clause): bool
	{
		foreach ($this->parts as [$c])
			if ($c === $clause)
				return true;

		return false;
	}

	/**
	 * Merges another {@see QueryBuilder}.
	 *
	 * @param QueryBuilder $builder
	 * @param int          $extraIndent
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function merge(QueryBuilder $builder, int $extraIndent = 0): void
	{
		foreach ($builder->parts as [$clause, $data, $indentSelf, $indent, $newLine, $separator])
			$this->parts[] = [$clause, $data, $indentSelf + $extraIndent, $indent + $extraIndent, $newLine, $separator];
	}

	/**
	 * Repeats a string x times.
	 *
	 * @param int    $times
	 * @param string $what
	 * @param string $str
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private final function repeat(int $times, string $what, string $str = ''): string
	{
		for ($i = 0; $i < $times; ++$i)
			$str .= $what;

		return $str;
	}

	/**
	 * Adds a JOIN clause.
	 *
	 * @param string        $clause
	 * @param string        $table
	 * @param callable|null $fn
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private final function _join(string $clause, string $table, ?callable $fn = null): self
	{
		$this->add($clause, $this->escapeField($table), 1, 0, 0, null);

		if ($fn !== null)
			$fn($this);

		return $this;
	}

	/**
	 * Adds a SELECT clause.
	 *
	 * @param string $clause
	 * @param array  ...$fields
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private final function _select(string $clause, ...$fields): self
	{
		if (count($fields) === 0)
			$fields[] = '*';

		$fields = array_map(function ($field): string
		{
			if (is_array($field) && count($field) === 2)
				return $this->escapeField($field[0]) . ' AS ' . $this->escapeField($field[1]);

			return $this->escapeField((string)$field);
		}, $fields);

		$this->add($clause, $fields, 0, 1, 1, self::DEFAULT_FIELD_SEPARATOR);

		return $this;
	}

	/**
	 * Creates a valid statement.
	 *
	 * @param string       $field
	 * @param string       $comparator
	 * @param string|array $value
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private final function _toStatement(string $field = '', string $comparator = '', $value = ''): string
	{
		if (empty($field))
			return '';

		if ($value === null)
			$value = [null, PDO::PARAM_NULL];

		$value = $this->addValue($value);

		if ($comparator === null)
			return $field;

		return $this->escapeField($field) . ' ' . $comparator . ' ' . $value;
	}

	/**
	 * Adds a conditional AND clause.
	 *
	 * @param bool         $condition
	 * @param string       $field
	 * @param string       $comparator
	 * @param string|array $value
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function conditionalAnd(bool $condition, string $field = '', string $comparator = '', $value = ''): self
	{
		if (!$condition)
			return $this;

		$this->and($field, $comparator, $value);

		return $this;
	}

	/**
	 * Adds a conditional OR clause.
	 *
	 * @param bool         $condition
	 * @param string       $field
	 * @param string       $comparator
	 * @param string|array $value
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function conditionalOr(bool $condition, string $field = '', string $comparator = '', $value = ''): self
	{
		if (!$condition)
			return $this;

		$this->or($field, $comparator, $value);

		return $this;
	}

	/**
	 * Adds conditional parenthesis.
	 *
	 * @param bool     $condition
	 * @param callable $fn
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function conditionalParenthesis(bool $condition, callable $fn): self
	{
		if (!$condition)
			return $this;

		$this->parentheses($fn);

		return $this;
	}

	/**
	 * Adds a conditional parenthesis.
	 *
	 * @param bool $condition
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function conditionalParenthesisClose(bool $condition): self
	{
		if (!$condition)
			return $this;

		$this->parenthesisClose();

		return $this;
	}

	/**
	 * Adds a conditional parenthesis.
	 *
	 * @param bool         $condition
	 * @param string       $field
	 * @param string       $comparator
	 * @param string|array $value
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function conditionalParenthesisOpen(bool $condition, string $field = '', string $comparator = '', $value = ''): self
	{
		if (!$condition)
			return $this;

		$this->parenthesisOpen($field, $comparator, $value);

		return $this;
	}

	/**
	 * Adds an AND clause.
	 *
	 * @param string       $field
	 * @param string       $comparator
	 * @param string|array $value
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function and(string $field = '', string $comparator = '', $value = ''): self
	{
		$statement = $this->_toStatement($field, $comparator, $value);
		$this->add('AND', $statement, 1, 0, ($statement === '' ? 0 : 1), '');

		return $this;
	}

	/**
	 * Creates a custom query.
	 *
	 * @param string $custom
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public function custom(string $custom): self
	{
		$this->add($custom, '', 0, 1, 1);

		return $this;
	}

	/**
	 * Creates a DELETE query.
	 *
	 * @param string $table
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public function delete(string $table): self
	{
		$this->add('DELETE', $this->escapeField($table), 0, 1, 1);

		return $this;
	}

	/**
	 * Creates a DELETE FROM query.
	 *
	 * @param string $table
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function deleteFrom(string $table): self
	{
		$this->add('DELETE FROM', $this->escapeField($table), 0, 1, 1);

		return $this;
	}

	/**
	 * Adds a FROM clause.
	 *
	 * @param string ...$tables
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function from(string ...$tables): self
	{
		$this->add('FROM', $this->escapeFields($tables), 0, 1, 1, ',');

		return $this;
	}

	/**
	 * Adds a GROUP BY clause.
	 *
	 * @param string ...$fields
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function groupBy(string ...$fields): self
	{
		$this->add('GROUP BY', $this->escapeFields($fields), 0, 1, 1, self::DEFAULT_FIELD_SEPARATOR);

		return $this;
	}

	/**
	 * Adds a HAVING clause.
	 *
	 * @param string       $field
	 * @param string       $comparator
	 * @param string|array $value
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function having(string $field = '', string $comparator = '', $value = ''): self
	{
		$statement = $this->_toStatement($field, $comparator, $value);
		$this->add('HAVING', $statement, 0, ($statement === '' ? 0 : 1), ($statement === '' ? 0 : 1), '');

		return $this;
	}

	/**
	 * Creates an INSERT IGNORE INTO query.
	 *
	 * @param string   $table
	 * @param string[] $fields
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function insertIgnoreInto(string $table, string ...$fields): self
	{
		$this->add('INSERT IGNORE INTO', $this->escapeField($table), 0, 1, 1);
		$this->parenthesisOpen();
		$this->add('', $this->escapeFields($fields), 0, 1, 1, self::DEFAULT_FIELD_SEPARATOR);
		$this->parenthesisClose();

		return $this;
	}

	/**
	 * Creates an INSERT INTO query.
	 *
	 * @param string   $table
	 * @param string[] $fields
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function insertInto(string $table, string ...$fields): self
	{
		$this->add('INSERT INTO', $this->escapeField($table), 0, 1, 1);
		$this->parenthesisOpen();
		$this->add('', $this->escapeFields($fields), 0, 1, 1, self::DEFAULT_FIELD_SEPARATOR);
		$this->parenthesisClose();

		return $this;
	}

	/**
	 * Creates an INSERT INTO {@see $table} (...) VALUES (...) query.
	 *
	 * @param string $table
	 * @param array  ...$data
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function insertIntoValues(string $table, array ...$data): self
	{
		$fields = [];
		$values = [];

		foreach ($data as $d)
		{
			$fields[] = array_shift($d);
			$values[] = count($d) === 2 ? $d : $d[0];
		}

		$this->insertInto($table, ...$fields);
		$this->values(...$values);

		return $this;
	}

	/**
	 * Creates a REPLACE INTO query.
	 *
	 * @param string $table
	 * @param string ...$fields
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function replaceInto(string $table, string ...$fields): self
	{
		$this->add('REPLACE INTO', $this->escapeField($table), 0, 1, 1);
		$this->parenthesisOpen();
		$this->add('', $this->escapeFields($fields), 0, 1, 1, self::DEFAULT_FIELD_SEPARATOR);
		$this->parenthesisClose();

		return $this;
	}

	/**
	 * Creates a REPLACE INTO {@see $table} (...) VALUES (...) query.
	 *
	 * @param string $table
	 * @param array  ...$data
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function replaceIntoValues(string $table, array ...$data): self
	{
		$fields = [];
		$values = [];

		foreach ($data as $d)
		{
			$fields[] = array_shift($d);
			$values[] = count($d) === 2 ? $d : $d[0];
		}

		$this->replaceInto($table, ...$fields);
		$this->values(...$values);

		return $this;
	}

	/**
	 * Adds a FULL JOIN clause.
	 *
	 * @param string        $table
	 * @param callable|null $fn
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function fullJoin(string $table, ?callable $fn = null): self
	{
		return $this->_join('FULL JOIN', $table, $fn);
	}

	/**
	 * Adds an INNER JOIN clause.
	 *
	 * @param string        $table
	 * @param callable|null $fn
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function innerJoin(string $table, ?callable $fn = null): self
	{
		return $this->_join('INNER JOIN', $table, $fn);
	}

	/**
	 * Adds a JOIN clause.
	 *
	 * @param string        $table
	 * @param callable|null $fn
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function join(string $table, ?callable $fn = null): self
	{
		return $this->_join('JOIN', $table, $fn);
	}

	/**
	 * Adds a LEFT JOIN clause.
	 *
	 * @param string        $table
	 * @param callable|null $fn
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function leftJoin(string $table, ?callable $fn = null): self
	{
		return $this->_join('LEFT JOIN', $table, $fn);
	}

	/**
	 * Adds a LEFT OUTER JOIN clause.
	 *
	 * @param string        $table
	 * @param callable|null $fn
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function leftOuterJoin(string $table, ?callable $fn = null): self
	{
		return $this->_join('LEFT OUTER JOIN', $table, $fn);
	}

	/**
	 * Adds a RIGHT JOIN clause.
	 *
	 * @param string        $table
	 * @param callable|null $fn
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function rightJoin(string $table, ?callable $fn = null): self
	{
		return $this->_join('RIGHT JOIN', $table, $fn);
	}

	/**
	 * Adds a LIMIT clause.
	 *
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function limit(int $limit, int $offset = 0): self
	{
		$this->add('LIMIT', $this->addValue([$limit, PDO::PARAM_INT]), 0, 1, 1);

		if ($offset > 0)
			$this->add('OFFSET', $this->addValue([$offset, PDO::PARAM_INT]), 0, 1, 1);

		return $this;
	}

	/**
	 * Adds an ON clause.
	 *
	 * @param string $field1
	 * @param string $comparator
	 * @param string $field2
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function on(string $field1, string $comparator, string $field2): self
	{
		$this->add('ON', $this->escapeField($field1) . ' ' . $comparator . ' ' . $this->escapeField($field2), 2, 0, 0, null);

		return $this;
	}

	/**
	 * Adds an ON DUPLICATE KEY UPDATE clause.
	 *
	 * @param string ...$fields
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function onDuplicateKeyUpdate(string ...$fields): self
	{
		$statement = [];

		foreach ($fields as $field)
			if (stristr($field, '='))
				$statement[] = $field;
			else
				$statement[] = $field . ' = VALUES(' . $field . ')';

		$this->add('ON DUPLICATE KEY UPDATE', $statement, 0, 1, 1, self::DEFAULT_FIELD_SEPARATOR);

		return $this;
	}

	/**
	 * Creates an OPTIMIZE TABLE query.
	 *
	 * @param string ...$table
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function optimizeTable(string ...$table): self
	{
		$this->add('OPTIMIZE TABLE', $this->escapeFields($table), 0, 1, 1, self::DEFAULT_FIELD_SEPARATOR);

		return $this;
	}

	/**
	 * Adds an OR clause.
	 *
	 * @param string       $field
	 * @param string       $comparator
	 * @param string|array $value
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function or(string $field = '', string $comparator = '', $value = ''): self
	{
		$statement = $this->_toStatement($field, $comparator, $value);
		$this->add('OR', $statement, 1, 0, ($statement === '' ? 0 : 1), '');

		return $this;
	}

	/**
	 * Adds an ORDER BY clause.
	 *
	 * @param string ...$fields
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function orderBy(string ...$fields): self
	{
		$fields = array_map(function ($field): string
		{
			if (strpos($field, ' ASC'))
			{
				$field = str_replace(' ASC', '', $field);

				return $this->escapeField($field) . ' ASC';
			}

			if (strpos($field, ' DESC'))
			{
				$field = str_replace(' DESC', '', $field);

				return $this->escapeField($field) . ' DESC';
			}

			return $this->escapeField($field);
		}, $fields);

		$this->add('ORDER BY', $fields, 0, 1, 1, self::DEFAULT_FIELD_SEPARATOR);

		return $this;
	}

	/**
	 * Wraps all calls within {@see $fn} within parentheses.
	 *
	 * @param callable $fn
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function parentheses(callable $fn): self
	{
		$firstIndex = count($this->parts);

		$this->parenthesisOpen();
		$fn($this);
		$this->parenthesisClose();

		$clause = $this->parts[$firstIndex + 1][0];
		$this->parts[$firstIndex][0] = $clause . ' ' . $this->parts[$firstIndex][0];
		$this->parts[$firstIndex + 1][0] = '';

		return $this;
	}

	/**
	 * Adds a close parenthesis.
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function parenthesisClose(): self
	{
		$this->indention--;

		$this->add(')', '', 1, 0, 0, '');

		return $this;
	}

	/**
	 * Adds an open parenthesis.
	 *
	 * @param string       $field
	 * @param string       $comparator
	 * @param string|array $value
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function parenthesisOpen(string $field = '', string $comparator = '', $value = ''): self
	{
		$statement = $this->_toStatement($field, $comparator, $value);
		$this->add('(', $statement, 1, ($statement === '' ? 0 : 1), ($statement === '' ? 0 : 1));

		++$this->indention;

		return $this;
	}

	/**
	 * Creates a SELECT query.
	 *
	 * @param array ...$fields
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function select(...$fields): self
	{
		return $this->_select('SELECT', ...$fields);
	}

	/**
	 * Creates a SELECT {@see $suffix} query.
	 *
	 * @param string $suffix
	 * @param array  ...$fields
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function selectCustom(string $suffix, ...$fields): self
	{
		return $this->_select('SELECT ' . $suffix, ...$fields);
	}

	/**
	 * Creates a SELECT DISTINCT query.
	 *
	 * @param array ...$fields
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function selectDistinct(...$fields): self
	{
		return $this->_select('SELECT DISTINCT', ...$fields);
	}

	/**
	 * Creates a SELECT SQL_CALC_FOUND_ROWS query.
	 *
	 * @param array ...$fields
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function selectFoundRows(...$fields): self
	{
		return $this->_select('SELECT SQL_CALC_FOUND_ROWS', ...$fields);
	}

	/**
	 * Adds a SET clause.
	 *
	 * @param string       $field
	 * @param string|array $value
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function set(string $field = '', $value = ''): self
	{
		$statement = $this->_toStatement($field, '=', $value);

		if ($this->previousClause === 'SET')
		{
			$index = count($this->parts) - 1;
			$existingStatement = $this->parts[$index][1];

			if (!is_array($existingStatement))
				$existingStatement = [$existingStatement];

			$existingStatement[] = $statement;

			$this->parts[$index][1] = $existingStatement;
		}
		else
		{
			$this->add('SET', $statement, 0, ($statement === '' ? 0 : 1), ($statement === '' ? 0 : 1), ', ');
		}

		return $this;
	}

	/**
	 * Creates a TRUNCATE TABLE query.
	 *
	 * @param string $table
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function truncateTable(string $table): self
	{
		$this->add('TRUNCATE TABLE', $this->escapeField($table), 0, 1, 1);

		return $this;
	}

	/**
	 * Adds an UNION statement.
	 *
	 * @param QueryBuilder $builder
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function union(QueryBuilder $builder): self
	{
		$this->add('UNION', '');
		$this->merge($builder);

		return $this;
	}

	/**
	 * Adds an UNION ALL statement.
	 *
	 * @param QueryBuilder $builder
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function unionAll(QueryBuilder $builder): self
	{
		$this->add('UNION ALL', '');
		$this->merge($builder);

		return $this;
	}

	/**
	 * Creates an UPDATE query.
	 *
	 * @param string $table
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function update(string $table): self
	{
		$this->add('UPDATE', $this->escapeField($table), 0, 1, 1);

		return $this;
	}

	/**
	 * Adds a VALUES clause.
	 *
	 * @param array ...$values
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function values(...$values): self
	{
		$vals = [];

		foreach ($values as $value)
			$vals[] = $this->addValue($value);

		$this->add($this->has('VALUES') ? ',' : 'VALUES', '');
		$this->parenthesisOpen();
		$this->add('', $vals, 0, 1, 1, self::DEFAULT_FIELD_SEPARATOR);
		$this->parenthesisClose();

		return $this;
	}

	/**
	 * Adds a WHERE clause.
	 *
	 * @param string       $field
	 * @param string       $comparator
	 * @param string|array $value
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function where(string $field = '', string $comparator = '', $value = ''): self
	{
		$statement = $this->_toStatement($field, $comparator, $value);
		$this->add('WHERE', $statement, 0, ($statement === '' ? 0 : 1), ($statement === '' ? 0 : 1), '');

		return $this;
	}

	/**
	 * Adds a WITH statement.
	 *
	 * @param string       $name
	 * @param QueryBuilder $query
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function with(string $name, QueryBuilder $query): self
	{
		if ($this->previousClause !== 'WITH')
			$this->add('WITH', "$name AS", 0, 0, -1);
		else
			$this->add(',', "$name AS", 0, 0);

		$this->parenthesisOpen();
		$this->merge($query, 1);
		$this->parenthesisClose();

		return $this;
	}

	/**
	 * Adds a WITH RECURSIVE statement.
	 *
	 * @param string       $name
	 * @param QueryBuilder $query
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function withRecursive(string $name, QueryBuilder $query): self
	{
		if ($this->previousClause !== 'WITH RECURSIVE')
			$this->add('WITH RECURSIVE', "$name AS", 0, 0, -1);
		else
			$this->add(',', "$name AS", 0, 0);

		$this->parenthesisOpen();
		$this->merge($query, 1);
		$this->parenthesisClose();

		return $this;
	}

	/**
	 * Removes a model association.
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function noModel(): self
	{
		$this->modelClass = null;

		return $this;
	}

	/**
	 * Associates a model with the query.
	 *
	 * @param string $modelClass
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function withModel(string $modelClass): self
	{
		$this->modelClass = $modelClass;

		return $this;
	}

	/**
	 * Debugs our building query.
	 *
	 * @param bool $pretty
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function debug(bool $pretty = true): array
	{
		$wasDebug = $this->pretty;
		$this->pretty = $pretty;
		$raw = $query = $this->__toString();

		foreach ($this->params as [$name, $value, $type])
		{
			if ($type === PDO::PARAM_STR)
				$value = $this->driver->quote($value);

			if ($type === PDO::PARAM_NULL && $value === null)
				$value = 'NULL';

			$query = str_replace(':' . $name, $value, $query);
		}

		$this->pretty = $wasDebug;

		return [
			'query' => $query,
			'query_raw' => $raw,
			'params' => $this->params
		];
	}

	/**
	 * Reports an unsupported feature with a DatabaseException.
	 *
	 * @param string $method
	 *
	 * @return mixed
	 * @throws DatabaseException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	protected final function unsupportedFeature(string $method): void
	{
		throw new DatabaseException(sprintf('Feature %s is currently not supported for %s.', $method, get_class($this->driver)), DatabaseException::ERR_FEATURE_UNSUPPORTED);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function __toString(): string
	{
		$query = [];

		if ($this->parts === null)
			throw new DatabaseException('Query not initialized!', DatabaseException::ERR_QUERY_FAILED);

		foreach ($this->parts as [$clause, $data, $indentSelf, $indent, $newLine, $separator])
		{
			if ($newLine > 0)
				$indent = $indentSelf + $indent;

			if (!$this->pretty)
			{
				$indentSelf = 0;
				$indent = 0;
				$newLine = 0;
			}

			if (is_array($data))
				$data = implode($separator . $this->repeat($newLine, PHP_EOL) . $this->repeat($indent, self::DEFAULT_INDENT), $data);

			$parts = [];

			if (!empty($clause))
				$parts[] = $this->repeat($indentSelf, self::DEFAULT_INDENT) . $clause . $this->repeat($newLine, PHP_EOL);

			$parts[] = $this->repeat($indent, self::DEFAULT_INDENT, ($newLine > 0 || empty($clause) || empty($data)) ? '' : ' ') . $data;
			$query[] = implode($parts);
		}

		return implode(($this->pretty ? PHP_EOL : ' '), $query) . PHP_EOL;
	}

}
