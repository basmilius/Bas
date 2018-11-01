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

namespace Columba\Util;

/**
 * Class A
 *
 * @author Bas Milius <bas@miliu.us>
 * @package Columba\Util
 * @since 1.3.0
 */
final class A
{

	/**
	 * Returns an array with all keys from {@see $arr} lowercased or uppercased. Numbered indices are left as is.
	 *
	 * @param array $arr
	 * @param int   $case
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function changeKeyCase(array $arr, int $case = CASE_LOWER): array
	{
		return array_change_key_case($arr, $case);
	}

	/**
	 * Chunks {@see $arr} into arrays with {@see $size} elements. The last chunk may contain less than {@see $size} elements.
	 *
	 * @param array $arr
	 * @param int   $size
	 * @param bool  $preserveKeys
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function chunk(array $arr, int $size, bool $preserveKeys = false): array
	{
		return array_chunk($arr, $size, $preserveKeys);
	}

	/**
	 * Returns the values from a single column of {@see $arr}, identified by {@see $column}. Optionally, an {@see $indexKey}
	 * may be provided to index the values in the returned array by the values from the {@see $indexKey} column of {@see $arr}.
	 *
	 * @param array $arr
	 * @param mixed $column
	 * @param mixed $indexKey
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function column(array $arr, $column, $indexKey = null): array
	{
		return array_column($arr, $column, $indexKey);
	}

	/**
	 * Creates an array by using the values from {@see $keys} as keys and the values from {@see $values} as the corresponding values.
	 *
	 * @param array $keys
	 * @param array $values
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function combine(array $keys, array $values): array
	{
		return array_combine($keys, $values);
	}

	/**
	 * Returns an array using the values of {@see $arr} as keys and their frequency in {@see $arr} as values.
	 *
	 * @param array $arr
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function countValues(array $arr): array
	{
		return array_count_values($arr);
	}

	/**
	 * Compares all {@see $arrs} against eachother and returns the values of the first array that are not present in the other arrays.
	 *
	 * @param array ...$arrs
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function diff(array ...$arrs): array
	{
		return array_diff(...$arrs);
	}

	/**
	 * Compares all {@see $arrs} against eachother and returns the difference. Unlike {@see array_diff()} the array keys are also
	 * used in comparison.
	 *
	 * @param array ...$arrs
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function diffAssoc(array ...$arrs): array
	{
		return array_diff_assoc(...$arrs);
	}

	/**
	 * Compares all keys in all {@see $arrs} against eachother and returns the difference. This function is like {@see array_diff()}
	 * except the comparison is done on the keys instead of the values.
	 *
	 * @param array ...$arrs
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function diffKey(array ...$arrs): array
	{
		return array_diff_key(...$arrs);
	}

	/**
	 * Compares all arrays against eachother and returns the difference. Unlike {@see array_diff()} the array keys are used in comparison.
	 * Unlike {@see array_diff_assoc()} an user supplied comparator is used for the indices comparison, not an internal function.
	 *
	 * @param array    $arrs
	 * @param callable $comparator
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function diffUAssoc(array $arrs, callable $comparator): array
	{
		$arrs[] = $comparator;

		return array_diff_uassoc(...$arrs);
	}

	/**
	 * Compares the keys from all {@see $arrs} against eachother and returns the difference. This function is like {@see array_diff()}
	 * except the comparison is done on the keys instead of the values.
	 * Unlike {@see array_diff_key()} an user supplied comparator is used for the indices comparison, not an internal function.
	 *
	 * @param array    $arrs
	 * @param callable $comparator
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function diffUKey(array $arrs, callable $comparator): array
	{
		$arrs[] = $comparator;

		return array_diff_ukey(...$arrs);
	}

	/**
	 * Fills an array with {@see $length} entries of {@see $value}, keys starting at the {@see $start} parameter.
	 *
	 * @param int   $start
	 * @param int   $length
	 * @param mixed $value
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function fill(int $start, int $length, $value): array
	{
		return array_fill($start, $length, $value);
	}

	/**
	 * Fills an array with {@see $value}, using the values of {@see $keys} as keys.
	 *
	 * @param array $keys
	 * @param mixed $value
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function fillKeys(array $keys, $value): array
	{
		return array_fill_keys($keys, $value);
	}

	/**
	 * Iterates over each value in {@see $arr} passing them to {@see $callback}. If {@see $callback} returns true, the
	 * current value from {@see $arr} is returned into the result array. Array keys are preserved.
	 *
	 * @param array         $arr
	 * @param callable|null $callback
	 * @param int           $flag
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function filter(array $arr, ?callable $callback = null, int $flag = 0): array
	{
		if ($callback === null)
			$callback = function ($item): bool
			{
				return !$item;
			};

		return array_filter($arr, $callback, $flag);
	}

	/**
	 * Returns {@see $arr} in flip order, i.e. keys from {@see $arr} become values and values from {@see $arr} become keys.
	 *
	 * @param array $arr
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function flip(array $arr): array
	{
		return array_flip($arr);
	}

	/**
	 * Returns an array containing all the values of the first array that are present in all other arrays. Keys are preserved.
	 *
	 * @param array ...$arrs
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function intersect(array ...$arrs): array
	{
		return array_intersect(...$arrs);
	}

	/**
	 * Returns an array containing all the values of the first array that are present in all other arrays. Keys are also used
	 * in this comparison.
	 *
	 * @param array ...$arrs
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function intersectAssoc(array ...$arrs): array
	{
		return array_intersect_assoc(...$arrs);
	}

	/**
	 * Returns an array containing all the entries of the first array which have keys that are present in all other arrays.
	 *
	 * @param array $arrs
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function intersectKey(array $arrs): array
	{
		return array_intersect_key(...$arrs);
	}

	/**
	 * Returns an array containing all values of the first array that are present in all other arrays. Note that keys are used
	 * in the comparison unline {@see array_intersect()}.
	 *
	 * @param array    $arrs
	 * @param callable $comparator
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function intersectUAssoc(array $arrs, callable $comparator): array
	{
		$arrs[] = $comparator;

		return array_intersect_uassoc(...$arrs);
	}

	/**
	 * Returns an array containing all values of the first array which have matching keys that are present in all other arrays.
	 *
	 * @param array    $arrs
	 * @param callable $comparator
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function intersectUKey(array $arrs, callable $comparator): array
	{
		$arrs[] = $comparator;

		return array_intersect_ukey(...$arrs);
	}

	/**
	 * Returns TRUE if the given {@see $key} is set in {@see $arr}. {@see $key} can be any value possible for an array index.
	 *
	 * @param array $arr
	 * @param mixed $key
	 *
	 * @return bool
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function keyExists(array $arr, $key): bool
	{
		return isset($arr[$key]) || array_key_exists($key, $arr);
	}

	/**
	 * Returns the keys of {@see $arr}. If {@see $searchValue} is specified, then only the keys for that value are returned.
	 * Otherwise all keys form {@see $arr} are returned.
	 *
	 * @param array $arr
	 * @param null  $searchValue
	 * @param bool  $strict
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function keys(array $arr, $searchValue = null, bool $strict = false): array
	{
		return array_keys($arr, $searchValue, $strict);
	}

	/**
	 * Returns an array containing all elements of {@see $arr} after applying {@see $callback} to each entry.
	 *
	 * @param array    $arr
	 * @param callable $callback
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function map(array $arr, callable $callback): array
	{
		return array_map($callback, $arr);
	}

	/**
	 * Returns an array containing all elements of {@see $arr} after applying {@see $callback} to each entry. The number of
	 * parameters that {@see $callback} accepts should match the number of {@see $arrs}.
	 *
	 * @param array    $arrs
	 * @param callable $callback
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function mapMultiple(array $arrs, callable $callback): array
	{
		return array_map($callback, ...$arrs);
	}

	/**
	 * Merges the entries of one or more arrays together so that the values of one are appended to the end of the previous
	 * one. It returns the resulting array. If the input arrays have the same string keys, then the later value for that key
	 * will overwrite the previous one. If, however, the arrays contain numeric keys, the later value will not overwrite the
	 * original value, but will be appended. Values in the input array with numeric keys will be renumbered with incrementing
	 * keys starting from zero in the result array.
	 *
	 * @param array ...$arrs
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function merge(array ...$arrs): array
	{
		return array_merge(...$arrs);
	}

	/**
	 * Merges the entries of one or more arrays together so that the values of one are appended to the end of the previous
	 * one. It returns the resulting array. If the input arrays have the same string keys, then the later value for that key
	 * will overwrite the previous one. If, however, the arrays contain numeric keys, the later value will not overwrite the
	 * original value, but will be appended.
	 *
	 * @param array ...$arrs
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function mergeRecursive(array ...$arrs): array
	{
		return array_merge_recursive(...$arrs);
	}

	// TODO(Bas): multiSort (http://php.net/manual/en/function.array-multisort.php)

	/**
	 * Returns a copy of {@see $arr} padded to {@see $size} with {@see $value}. If {@see $size} is positive then the array is
	 * padded on the right, if it's negative then on the left. If the absolute value of {@see $size} is less than or equal to
	 * the length of {@see $arr} then no padding takes place. It's posible to add at most 1048576 elements at a time.
	 *
	 * @param array $arr
	 * @param int   $size
	 * @param mixed $value
	 *
	 * @return array
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function pad(array $arr, int $size, $value): array
	{
		return array_pad($arr, $size, $value);
	}

	/**
	 * Pops and returns the last value of {@see $arr}, shortening {@see $arr} by one entry.
	 *
	 * @param array $arr
	 *
	 * @return mixed
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function pop(array &$arr)
	{
		return array_pop($arr);
	}

	/**
	 * Returns the product of values in {@see $arr}.
	 *
	 * @param array $arr
	 *
	 * @return float|int
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function product(array $arr)
	{
		return array_product($arr);
	}

	/**
	 * Push one or more entries onto the end of {@see $arr}.
	 *
	 * @param array $arr
	 * @param mixed ...$values
	 *
	 * @return int
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function push(array &$arr, ...$values): int
	{
		return array_push($arr, ...$values);
	}

	/**
	 * Picks one or more random entries our of {@see $arr}, and returns the key (or keys) of the random entries. It
	 * uses a pseudo random number generator that is not suitable for cryptographic purposes.
	 *
	 * @param array $arr
	 * @param int   $num
	 *
	 * @return mixed
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function random(array $arr, int $num = 1)
	{
		return array_rand($arr, $num);
	}

	/**
	 * Applies interatively the {@see $callback} function to the entries of {@see $arr}, so as to reduce the array
	 * to a single value.
	 *
	 * @param array    $arr
	 * @param mixed    $initial
	 * @param callable $callback
	 *
	 * @return mixed
	 * @author Bas Milius <bas@miliu.us>
	 * @since 1.3.0
	 */
	public static function reduce(array $arr, $initial = null, ?callable $callback = null)
	{
		return array_reduce($arr, $callback, $initial);
	}

}
