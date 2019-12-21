<?php
/**
 * Copyright (c) 2017 - 2019 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Latte Framework package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Database\Query\Builder;

use Columba\Data\Collection;
use Columba\Database\Connection\Connection;
use Columba\Database\Dialect\Dialect;
use Columba\Database\Model\Model;
use Columba\Database\Query\Statement;
use Columba\Database\Util\BuilderUtil;
use Generator;
use function count;
use function implode;
use function is_array;
use function is_string;
use function strlen;
use function strpos;

/**
 * Class Base
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Query\Builder
 * @since 1.6.0
 */
class Base
{

	private static int $num = 0;

	protected ?Connection $connection;
	protected ?Dialect $dialect;

	protected string $lastClause = '';
	protected array $pieces = [];

	protected ?string $modelClass = null;
	protected ?array $modelArguments = null;

	private int $indent = 0;
	private array $params = [];

	/**
	 * Base constructor.
	 *
	 * @param Connection|null $connection
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(?Connection $connection = null)
	{
		++static::$num;

		if (static::$num === 1)
			require_once __DIR__ . '/functions.php';

		if ($connection !== null)
			$this->setConnection($connection);
	}

	/**
	 * Puts the pieces together and builds the final query.
	 *
	 * @param bool $pretty
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function build(bool $pretty = false): string
	{
		$query = [];

		foreach ($this->pieces as [$clause, $data, $indentSelf, $indent, $newLine, $separator])
		{
			if ($newLine > 0)
				$indent = $indentSelf + $indent;

			if (!$pretty)
			{
				$indentSelf = 0;
				$indent = 0;
				$newLine = 0;
			}

			if (is_array($data))
				$data = implode($separator . BuilderUtil::repeat(PHP_EOL, $newLine) . BuilderUtil::repeat($this->dialect->indentation, $indent), $data);

			$pieces = [];

			if (!empty($clause))
				$pieces[] = BuilderUtil::repeat($this->dialect->indentation, $indentSelf) . $clause . BuilderUtil::repeat(PHP_EOL, $newLine);

			$pieces[] = ($newLine > 0 || empty($clause) || empty($data) ? '' : ' ') . BuilderUtil::repeat($this->dialect->indentation, $indent) . $data;
			$query[] = implode('', $pieces);
		}

		return implode($pretty ? PHP_EOL : ' ', $query);
	}

	/**
	 * Resets the builder.
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected final function reset(): self
	{
		$this->indent = 0;
		$this->lastClause = '';
		$this->modelClass = null;
		$this->modelArguments = null;
		$this->params = [];
		$this->pieces = [];

		return $this;
	}

	/**
	 * Sets the connection.
	 *
	 * @param Connection $connection
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected final function setConnection(Connection $connection): void
	{
		$this->connection = $connection;
		$this->dialect = $connection->getDialect();
	}

	/**
	 * Adds an expression.
	 *
	 * @param string      $clause
	 * @param string|null $column
	 * @param mixed       $comparator
	 * @param mixed       $value
	 * @param bool        $addParam
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @codeCoverageIgnore
	 * @internal
	 */
	public function addExpression(string $clause, ?string $column = null, $comparator = null, $value = null, bool $addParam = true): self
	{
		if ($value === null && $comparator !== null)
		{
			$value = $comparator;
			$comparator = '=';
		}

		if ($value instanceof ComparatorAwareLiteral)
		{
			$comparator = null;
			$value = $value->value($this);
		}
		else if ($value instanceof Literal)
		{
			$value = $value->value($this);
		}
		else if ($addParam && $comparator !== null)
		{
			$value = $this->addParam($value);
		}

		if ($column !== null)
		{
			if ($comparator === null && $value !== null)
				$expression = $this->dialect->escapeColumn($column) . ' ' . $value;
			else if ($comparator === null)
				$expression = $column;
			else
				$expression = $this->dialect->escapeColumn($column) . ' ' . $comparator . ' ' . $value;
		}
		else
		{
			$expression = '';
		}

		return $this->addPiece($clause, $expression, 0, ($expression === '' ? 0 : 1), ($expression === '' ? 0 : 1));
	}

	/**
	 * Adds a piece.
	 *
	 * @param string      $clause
	 * @param             $data
	 * @param int         $indentSelf
	 * @param int         $indent
	 * @param int         $newLine
	 * @param string|null $separator
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @codeCoverageIgnore
	 * @internal
	 */
	public function addPiece(string $clause, $data, int $indentSelf = 0, int $indent = 0, int $newLine = 0, ?string $separator = null): self
	{
		$this->pieces[] = [$clause, $data, $indentSelf + $this->indent, $indent, $newLine, $separator];

		if (strlen($clause) > 1)
			$this->lastClause = $clause;

		return $this;
	}

	/**
	 * Adds a value param.
	 *
	 * @param mixed $value
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @codeCoverageIgnore
	 * @internal
	 */
	public function addParam($value)
	{
		if ($value instanceof Value)
			return $value->value($this);

		if (is_array($value) && count($value) === 2)
			$param = $value;
		else if (is_bool($value))
			return $value ? 1 : 0;
		else if (is_string($value) && strpos($value, '(')) // TODO(Bas): Map all sql functions and proper check this.
			return $value;
		else
			$param = [$value, null];

		$name = 'p' . static::$num . '_' . count($this->params);
		$this->params[] = [$name, $param];

		return ':' . $name;
	}

	/**
	 * Executes the {@see Statement} and returns an array containing all results.
	 *
	 * @param array    $options
	 * @param int|null $foundRows
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function array(array $options = [], ?int &$foundRows = null): array
	{
		return $this->statement($options)->array(true, $foundRows);
	}

	/**
	 * Executes the {@see Statement} and returns a {@see CollectionResult}.
	 *
	 * @param array    $options
	 * @param int|null $foundRows
	 *
	 * @return Collection
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function collection(array $options = [], ?int &$foundRows = null): Collection
	{
		return $this->statement($options)->collection(true, $foundRows);
	}

	/**
	 * Executes the {@see Statement} and returns a {@see Generator} containing each result.
	 *
	 * @param array    $options
	 * @param int|null $foundRows
	 *
	 * @return Generator
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function cursor(array $options = [], ?int &$foundRows = null): Generator
	{
		yield from $this->statement($options)->cursor(true, $foundRows);
	}

	/**
	 * Executes the {@see Statement} and returns nothing.
	 *
	 * @param array $options
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function run(array $options = []): void
	{
		$this->statement($options)->run();
	}

	/**
	 * Executes the {@see Statement} and returns a single result.
	 *
	 * @param array $options
	 *
	 * @return Model|array|null|mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function single(array $options = [])
	{
		return $this->statement($options)->single();
	}

	/**
	 * Creates and executes a {@see Statement} and returns it.
	 *
	 * @param array $options
	 *
	 * @return Statement
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function statement(array $options = []): Statement
	{
		$statement = $this->connection->prepare($this->build(), $options);
		$statement->model($this->modelClass, $this->modelArguments);

		foreach ($this->params as [$name, $param])
			$statement->bind($name, $param[0], $param[1] ?? null);

		return $statement;
	}

	/**
	 * Assigns a model to the query result.
	 *
	 * @param string|null $class
	 * @param array|null  $arguments
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function model(?string $class, ?array $arguments = null): self
	{
		$this->modelClass = $class;
		$this->modelArguments = $arguments;

		return $this;
	}

	/**
	 * Runs the given function, only if the given condition is TRUE.
	 *
	 * @param bool     $condition
	 * @param callable $fn
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function conditional(bool $condition, callable $fn): self
	{
		if ($condition)
			$fn($this);

		return $this;
	}

	/**
	 * Runs the given function and wraps it with parenthesis, only if the given condition is TRUE.
	 *
	 * @param bool     $condition
	 * @param callable $fn
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function conditionalParenthesis(bool $condition, callable $fn): self
	{
		if ($condition)
			return $this->parenthesis($fn);

		return $this;
	}

	/**
	 * Adds the given custom expression to the query.
	 *
	 * @param string $expression
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function custom(string $expression): self
	{
		return $this->addPiece($expression, '', 0, 1, 1);
	}

	/**
	 * Wraps the given function with parenthesis.
	 *
	 * @param callable $fn
	 * @param bool     $fixClauses
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function parenthesis(callable $fn, bool $fixClauses = true): self
	{
		$index = count($this->pieces);

		$this->parenthesisOpen();
		$fn($this);
		$this->parenthesisClose();

		if ($fixClauses)
		{
			$clause = $this->pieces[$index + 1][0];
			$this->pieces[$index][0] = (!empty($clause) ? $clause . ' ' : '') . $this->pieces[$index][0];
			$this->pieces[$index + 1][0] = '';
		}

		return $this;
	}

	/**
	 * Closes a parenthesis group.
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function parenthesisClose(): self
	{
		$this->outdent();

		return $this->addPiece(')', '', 0);
	}

	/**
	 * Opens a parenthesis group.
	 *
	 * @param string|null $column
	 * @param string|null $comparator
	 * @param null        $value
	 * @param bool        $addParam
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function parenthesisOpen(?string $column = null, ?string $comparator = null, $value = null, bool $addParam = true)
	{
		$this->addExpression('(', $column, $comparator, $value, $addParam);
		$this->indent();

		return $this;
	}

	/**
	 * Returns TRUE if the given clause is available in the query.
	 *
	 * @param string $clause
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function hasClause(string $clause): bool
	{
		foreach ($this->pieces as [$c])
			if ($c === $clause)
				return true;

		return false;
	}

	/**
	 * Indent.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function indent(): void
	{
		++$this->indent;
	}

	/**
	 * Outdent.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function outdent(): void
	{
		--$this->indent;
	}

	/**
	 * Merges another {@see Base} builder.
	 *
	 * @param Base $other
	 * @param int  $extraIndent
	 *
	 * @return $this
	 * @since 1.6.0
	 * @author Bas Milius <bas@mili.us>
	 */
	protected function merge(self $other, int $extraIndent = 0): self
	{
		foreach ($other->pieces as [$clause, $data, $indentSelf, $indent, $newLine, $separator])
			$this->pieces[] = [$clause, $data, $indentSelf + $extraIndent, $indent + $extraIndent, $newLine, $separator];

		foreach ($other->params as $param)
			$this->params[] = $param;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __debugInfo(): array
	{
		return [
			'query' => $this->build()
		];
	}

}
