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

namespace Columba\Util;

use function array_map;
use function array_pop;
use function count;
use function explode;
use function implode;
use function join;
use function mb_strlen;
use function mb_strtolower;
use function mb_substr;
use function preg_match_all;
use function preg_replace;
use function preg_split;
use function strtolower;
use function transliterator_transliterate;
use function trim;

/**
 * Class StringUtil
 *
 * @package Columba\Util
 * @author Bas Milius <bas@mili.us>
 * @since 1.4.0
 */
final class StringUtil
{

	/**
	 * Implodes commas between strings and replaces the last comma with an &.
	 *
	 * @param string ...$strings
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function commaCommaAnd(string ...$strings): string
	{
		return preg_replace('/(.*),/', '$1 &', implode(', ', $strings));
	}

	/**
	 * Returns TRUE if {@see $str} ends with {@see $end}.
	 *
	 * @param string $str
	 * @param string $end
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function endsWith(string $str, string $end): bool
	{
		return mb_substr($str, -mb_strlen($end)) === $end;
	}

	/**
	 * Slugifies a string.
	 *
	 * @param string $str
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function slugify(string $str): string
	{
		$str = preg_replace('~[^\pL\d]+~u', '-', $str);
		$str = transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $str);
		$str = preg_replace('~[^-\w]+~', '', $str);
		$str = trim($str, '-');
		$str = preg_replace('~-+~', '-', $str);
		$str = mb_strtolower($str);

		return !empty($str) ? $str : '';
	}

	/**
	 * Splits text into sentences.
	 *
	 * @param string $str
	 *
	 * @return string[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public static function splitSentences(string $str): array
	{
		return preg_split('/(?<!\.\.\.)(?<!Dr\.)(?<=[.?!]|\.\)|\.")\s+(?=[a-zA-Z"(])/', $str);
	}

	/**
	 * Returns TRUE if {@see $str} starts with {@see $start}.
	 *
	 * @param string $str
	 * @param string $start
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function startsWith(string $str, string $start): bool
	{
		return mb_substr($str, 0, mb_strlen($start)) === $start;
	}

	/**
	 * Converts a string to pascal case.
	 *
	 * @param string $str
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public static function toPascalCase(string $str): string
	{
		preg_match_all('/([a-zA-Z0-9]+)/', $str, $matches);

		return join(array_map('ucfirst', $matches[0]));
	}

	/**
	 * Converts a string to snake case.
	 *
	 * @param string $str
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function toSnakeCase(string $str): string
	{
		return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $str));
	}

	/**
	 * Creates an excerpt.
	 *
	 * @param string $text
	 * @param int    $wordCount
	 * @param string $ending
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function truncateText(string $text, int $wordCount = 20, string $ending = '&hellip;'): string
	{
		$excerpt = $text;
		$excerpt = preg_replace("/<h2>.+?<\/h2>/i", "", $excerpt);
		$excerpt = preg_replace("/<h3>.+?<\/h3>/i", "", $excerpt);
		$words = explode(' ', $excerpt, $wordCount + 1);

		if (count($words) > $wordCount)
		{
			array_pop($words);
			$excerpt = implode(' ', $words);
			$excerpt .= $ending;
		}

		return $excerpt;
	}

}
