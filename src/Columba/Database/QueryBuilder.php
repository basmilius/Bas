<?php
/**
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Database;

use PDO;
use PDOException;

/**
 * Class QueryBuilder
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database
 * @since 1.0.0
 */
final class QueryBuilder
{

	private const DEFAULT_FIELD_SEPARATOR = ", ";
	private const DEFAULT_INDENT = "    ";

	/**
	 * @var AbstractDatabaseDriver
	 */
	private $driver;

	/**
	 * @var int
	 */
	private $indention = 0;

	/**
	 * @var array
	 */
	private $params = [];

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
	public function __construct (?AbstractDatabaseDriver $driver)
	{
		$this->driver = $driver;
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
	private final function add (string $clause, $data, int $indentSelf = 0, int $indent = 0, int $newLine = 0, ?string $separator = null): void
	{
		$this->parts[] = [$clause, $data, $indentSelf + $this->indention, $indent, $newLine, $separator];
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
	private final function addValue ($value)
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
	public final function escapeField (string $field): string
	{
		if (strpos($field, ' ') || strpos($field, '(') || strpos($field, ')'))
			return $field; // To hard to handle.

		$ignore = ['1', '*'];

		$parts = explode('.', $field);
		$parts = array_map(function (string $field) use ($ignore): string
		{
			if (in_array($field, $ignore))
				return $field;

			return '`' . $field . '`';
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
	public final function escapeFields (array $fields): array
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
	public final function execute (): ResultSet
	{
		$smt = $this->driver->prepare($this->__toString());

		foreach ($this->params as [$name, $value, $type])
			$smt->bind($name, $value, $type);

		return $smt->execute();
	}

	/**
	 * Initializes the query.
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private final function init (): self
	{
		$this->parts = [];

		return $this;
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
	private final function repeat (int $times, string $what, string $str = ''): string
	{
		for ($i = 0; $i < $times; $i++)
			$str .= $what;

		return $str;
	}

	/**
	 * Adds a JOIN clause.
	 *
	 * @param string $clause
	 * @param string $table
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private final function _join (string $clause, string $table): self
	{
		$this->add($clause, $this->escapeField($table), 1, 0, 0, null);

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
	private final function _select (string $clause, ...$fields): self
	{
		$this->init();

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
	private final function _toStatement (string $field = '', string $comparator = '', $value = ''): string
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
	 * @param bool   $condition
	 * @param string $field
	 * @param string $comparator
	 * @param string $value
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function conditionalAnd (bool $condition, string $field = '', string $comparator = '', $value = ''): self
	{
		if (!$condition)
			return $this;

		$this->and($field, $comparator, $value);

		return $this;
	}

	/**
	 * Adds a conditional OR clause.
	 *
	 * @param bool   $condition
	 * @param string $field
	 * @param string $comparator
	 * @param string $value
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function conditionalOr (bool $condition, string $field = '', string $comparator = '', $value = ''): self
	{
		if (!$condition)
			return $this;

		$this->or($field, $comparator, $value);

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
	public final function conditionalParenthesisClose (bool $condition): self
	{
		if (!$condition)
			return $this;

		$this->parenthesisClose();

		return $this;
	}

	/**
	 * Adds a conditional parenthesis.
	 *
	 * @param bool   $condition
	 * @param string $field
	 * @param string $comparator
	 * @param string $value
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function conditionalParenthesisOpen (bool $condition, string $field = '', string $comparator = '', $value = ''): self
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
	public final function and (string $field = '', string $comparator = '', $value = ''): self
	{
		$statement = $this->_toStatement($field, $comparator, $value);
		$this->add('AND', $statement, 1, 0, ($statement === '' ? 0 : 1), '');

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
	public final function delete (string $table): self
	{
		$this->init();
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
	public final function deleteFrom (string $table): self
	{
		$this->init();
		$this->add('DELETE FROM', $this->escapeField($table), 0, 1, 1);

		return $this;
	}

	/**
	 * Adds a FROM clause.
	 *
	 * @param string $table
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function from (string $table): self
	{
		$this->add('FROM', $this->escapeField($table), 0, 1, 1, null);

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
	public final function groupBy (string ...$fields): self
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
	public final function having (string $field = '', string $comparator = '', $value = ''): self
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
	public final function insertIgnoreInto (string $table, string ...$fields): self
	{
		$this->init();
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
	public final function insertInto (string $table, string ...$fields): self
	{
		$this->init();
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
	public final function insertIntoValues (string $table, array ...$data): self
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
	 * Adds a LEFT JOIN clause.
	 *
	 * @param string $table
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function leftJoin (string $table): self
	{
		return $this->_join('LEFT JOIN', $table);
	}

	/**
	 * Adds a LEFT OUTER JOIN clause.
	 *
	 * @param string $table
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function leftOuterJoin (string $table): self
	{
		return $this->_join('LEFT OUTER JOIN', $table);
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
	public final function limit (int $limit, int $offset = 0): self
	{
		$offset = $this->addValue([$offset, PDO::PARAM_INT]);
		$limit = $this->addValue([$limit, PDO::PARAM_INT]);

		$this->add('LIMIT', $offset . ', ' . $limit, 0, 1, 1);

		return $this;
	}

	/**
	 * Adds a JOIN clause.
	 *
	 * @param string $table
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function join (string $table): self
	{
		return $this->_join('JOIN', $table);
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
	public final function on (string $field1, string $comparator, string $field2): self
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
	public final function onDuplicateKeyUpdate (string ...$fields): self
	{
		$statement = [];

		foreach ($fields as $field)
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
	public final function optimizeTable (string ...$table): self
	{
		$this->init();
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
	public final function or (string $field = '', string $comparator = '', $value = ''): self
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
	public final function orderBy (string ...$fields): self
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

			return $field;
		}, $fields);

		$this->add('ORDER BY', $fields, 0, 1, 1, self::DEFAULT_FIELD_SEPARATOR);

		return $this;
	}

	/**
	 * Adds a close parenthesis.
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function parenthesisClose (): self
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
	public final function parenthesisOpen (string $field = '', string $comparator = '', $value = ''): self
	{
		$statement = $this->_toStatement($field, $comparator, $value);
		$this->add('(', $statement, 1, ($statement === '' ? 0 : 1), ($statement === '' ? 0 : 1));

		$this->indention++;

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
	public final function select (...$fields): self
	{
		return $this->_select('SELECT', ...$fields);
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
	public final function selectDistinct (...$fields): self
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
	public final function selectFoundRows (...$fields): self
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
	public final function set (string $field = '', $value = ''): self
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
	public final function truncateTable (string $table): self
	{
		$this->init();
		$this->add('TRUNCATE TABLE', $this->escapeField($table), 0, 1, 1);

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
	public final function update (string $table): self
	{
		$this->init();
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
	public final function values (...$values): self
	{
		$vals = [];

		foreach ($values as $value)
			$vals[] = $this->addValue($value);

		$this->add('VALUES', '');
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
	public final function where (string $field = '', string $comparator = '', $value = ''): self
	{
		$statement = $this->_toStatement($field, $comparator, $value);
		$this->add('WHERE', $statement, 0, ($statement === '' ? 0 : 1), ($statement === '' ? 0 : 1), '');

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
	public final function debug (bool $pretty = true): array
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
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function __toString (): string
	{
		$query = [];

		if ($this->parts === null)
			throw new PDOException('Query not initialized!', 0xDBA0007);

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
			$query[] = implode('', $parts);
		}

		return implode(($this->pretty ? PHP_EOL : ' '), $query) . PHP_EOL;
	}

}
