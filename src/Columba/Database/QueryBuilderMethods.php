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
 * Trait QueryBuilderMethods
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database
 * @since 1.5.0
 */
trait QueryBuilderMethods
{

	/**
	 * @var AbstractDatabaseDriver
	 */
	protected $driver;

	/**
	 * Creates a custom query.
	 *
	 * @param string $custom
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function custom(string $custom): QueryBuilder
	{
		$builder = $this->driver->createQueryBuilder();
		$builder->custom($custom);

		return $builder;
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
	public final function delete(string $table): QueryBuilder
	{
		$builder = $this->driver->createQueryBuilder();
		$builder->delete($table);

		return $builder;
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
	public final function deleteFrom(string $table): QueryBuilder
	{
		$builder = $this->driver->createQueryBuilder();
		$builder->deleteFrom($table);

		return $builder;
	}

	/**
	 * Creates an INSERT IGNORE INTO query.
	 *
	 * @param string $table
	 * @param string ...$fields
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function insertIgnoreInto(string $table, string ...$fields): QueryBuilder
	{
		$builder = $this->driver->createQueryBuilder();
		$builder->insertIgnoreInto($table, ...$fields);

		return $builder;
	}

	/**
	 * Creates an INSERT INTO query.
	 *
	 * @param string $table
	 * @param string ...$fields
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function insertInto(string $table, string ...$fields): QueryBuilder
	{
		$builder = $this->driver->createQueryBuilder();
		$builder->insertInto($table, ...$fields);

		return $builder;
	}

	/**
	 * Creates an INSERT INTO (...) VALUES (...) query.
	 *
	 * @param string $table
	 * @param array  ...$data
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function insertIntoValues(string $table, array ...$data): QueryBuilder
	{
		$builder = $this->driver->createQueryBuilder();
		$builder->insertIntoValues($table, ...$data);

		return $builder;
	}

	/**
	 * Creates an REPLACE INTO query.
	 *
	 * @param string $table
	 * @param string ...$fields
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function replaceInto(string $table, string ...$fields): QueryBuilder
	{
		$builder = $this->driver->createQueryBuilder();
		$builder->replaceInto($table, ...$fields);

		return $builder;
	}

	/**
	 * Creates an REPLACE INTO (...) VALUES (...) query.
	 *
	 * @param string $table
	 * @param array  ...$data
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function replaceIntoValues(string $table, array ...$data): QueryBuilder
	{
		$builder = $this->driver->createQueryBuilder();
		$builder->replaceIntoValues($table, ...$data);

		return $builder;
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
	public final function optimizeTable(string ...$table): QueryBuilder
	{
		$builder = $this->driver->createQueryBuilder();
		$builder->optimizeTable(...$table);

		return $builder;
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
	public final function select(...$fields): QueryBuilder
	{
		$builder = $this->driver->createQueryBuilder();
		$builder->select(...$fields);

		return $builder;
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
	public final function selectCustom(string $suffix, ...$fields): QueryBuilder
	{
		$builder = $this->driver->createQueryBuilder();
		$builder->selectCustom($suffix, ...$fields);

		return $builder;
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
	public final function selectDistinct(...$fields): QueryBuilder
	{
		$builder = $this->driver->createQueryBuilder();
		$builder->selectDistinct(...$fields);

		return $builder;
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
	public final function selectFoundRows(...$fields): QueryBuilder
	{
		$builder = $this->driver->createQueryBuilder();
		$builder->selectFoundRows(...$fields);

		return $builder;
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
	public final function truncateTable(string $table): QueryBuilder
	{
		$builder = $this->driver->createQueryBuilder();
		$builder->truncateTable($table);

		return $builder;
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
	public final function update(string $table): QueryBuilder
	{
		$builder = $this->driver->createQueryBuilder();
		$builder->update($table);

		return $builder;
	}

	/**
	 * Creates a WITH query.
	 *
	 * @param string       $name
	 * @param QueryBuilder $query
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function with(string $name, QueryBuilder $query): QueryBuilder
	{
		$builder = $this->driver->createQueryBuilder();
		$builder->with($name, $query);

		return $builder;
	}

	/**
	 * Creates a WITH RECURSIVE query.
	 *
	 * @param string       $name
	 * @param QueryBuilder $query
	 *
	 * @return QueryBuilder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function withRecursive(string $name, QueryBuilder $query): QueryBuilder
	{
		$builder = $this->driver->createQueryBuilder();
		$builder->withRecursive($name, $query);

		return $builder;
	}

}
