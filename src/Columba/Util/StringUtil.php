<?php
declare(strict_types=1);

namespace Columba\Util;

/**
 * Class StringUtil
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Util
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
