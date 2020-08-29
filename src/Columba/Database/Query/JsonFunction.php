<?php
declare(strict_types=1);

namespace Columba\Database\Query;

use Columba\Database\Query\Builder\Literal;

/**
 * Class JsonFunction
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Query
 * @since 1.6.0
 */
final class JsonFunction
{

	/**
	 * Returns a JSON_ARRAY literal.
	 *
	 * @param array $values
	 *
	 * @return Literal
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function array(array $values): Literal
	{
		$result = implode(',', $values);

		return new Literal("JSON_ARRAY($result)");
	}

	/**
	 * Returns a JSON_OBJECT literal.
	 *
	 * @param array $pairs
	 *
	 * @return Literal
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function object(array $pairs): Literal
	{
		$result = [];

		foreach ($pairs as $key => $value)
			$result[] = "'$key', $value";

		$result = implode(',', $result);

		return new Literal("JSON_OBJECT($result)");
	}

}
