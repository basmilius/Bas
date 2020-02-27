<?php
/**
 * Copyright (c) 2017 - 2020 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Latte Framework package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Internationalization;

use Closure;
use Columba\Facade\Debuggable;
use Columba\Util\ArrayUtil;
use function array_map;
use function count;
use function file_get_contents;
use function in_array;
use function is_array;
use function is_string;
use function json_decode;
use function rtrim;
use function vsprintf;

/**
 * Class Domain
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Internationalization
 * @since 1.6.0
 */
class Domain implements Debuggable
{

	private const SPECIAL_KEYS = ['_', '@'];

	protected Internationalization $i18n;
	protected Locale $locale;
	protected string $path;

	protected string $id;

	protected array $strings = [];

	/**
	 * Domain constructor.
	 *
	 * @param Internationalization $i18n
	 * @param Locale               $locale
	 * @param string               $path
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(Internationalization $i18n, Locale $locale, string $path)
	{
		$this->i18n = $i18n;
		$this->locale = $locale;
		$this->path = $path;

		$jsonData = json_decode(file_get_contents($path), true);

		$this->id = $jsonData['id'];
		$this->handleStrings($jsonData['strings']);
	}

	/**
	 * Gets all available strings in the domain.
	 *
	 * @return string[]|string[][]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getAvailableStrings(): array
	{
		return array_map(function ($string)
		{
			if ($string instanceof Closure)
			{
				return [
					$string(0),
					$string(1),
					$string(2)
				];
			}

			return $string;
		}, $this->strings);
	}

	/**
	 * Gets the id.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * Gets the strings.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getStrings(): array
	{
		return $this->strings;
	}

	/**
	 * Translates the given key and applies the given arguments.
	 *
	 * @param string $key
	 * @param mixed  ...$arguments
	 *
	 * @return string|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function translate(string $key, ...$arguments): ?string
	{
		if (!isset($this->strings[$key]))
			return null;

		$string = $this->strings[$key];

		if ($string instanceof Closure)
		{
			$count = $arguments[0] ?? 0;
			$string = $string($count);
		}

		if (count($arguments) === 0)
			return $string;

		return vsprintf($string, $arguments) ?: $key;
	}

	/**
	 * Converts the strings.
	 *
	 * @param array  $strings
	 * @param string $prefix
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private function handleStrings(array $strings, string $prefix = ''): void
	{
		foreach ($strings as $key => $string)
		{
			$realKey = $key;

			if (in_array($key, self::SPECIAL_KEYS))
				$key = rtrim($prefix, '.');
			else
				$key = $prefix . $key;

			if (is_string($string))
			{
				$this->strings[$key] = $string;
			}
			else if (is_array($string))
			{
				if (ArrayUtil::isSequentialArray($string))
				{
					if ($realKey === '@')
						$this->strings[$key] = $string;
					else
						$this->strings[$key] = fn(int $count) => $count === 0 ? $string[0] : ($count === 1 ? $string[1] : $string[2]);
				}
				else
				{
					$this->handleStrings($string, $key . '.');
				}
			}
		}
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __debugInfo(): ?array
	{
		return [
			'path' => $this->path,
			'id' => $this->id,
			'locale' => $this->locale,
			'strings' => $this->strings
		];
	}

}
