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

use function is_file;
use function realpath;
use function sprintf;

/**
 * Class Internationalization
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Internationalization
 * @since 1.6.0
 */
class Internationalization
{

	protected ?Locale $current = null;

	/** @var Locale[] */
	protected array $locales = [];

	/** @var string[] */
	protected array $paths = [];

	/**
	 * Adds a path where locales and domains can be.
	 *
	 * @param string $path
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function addPath(string $path): void
	{
		$path = realpath($path) ?: null;

		if ($path === null)
			throw new InternationalizationException('The given path does not exist.', InternationalizationException::ERR_INVALID_PATH);

		$this->paths[] = $path;
	}

	/**
	 * Gets the current locale.
	 *
	 * @return Locale|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getCurrentLocale(): ?Locale
	{
		return $this->current;
	}

	/**
	 * Gets the current locale id.
	 *
	 * @return string|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getCurrentLocaleId(): ?string
	{
		return $this->current !== null ? $this->current->getId() : null;
	}

	/**
	 * Gets a locale or loads one if it isn't already.
	 *
	 * @param string $locale
	 *
	 * @return Locale
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getLocale(string $locale): Locale
	{
		if ($this->isLocaleLoaded($locale))
			return $this->locales[$locale];

		return $this->loadLocale($locale);
	}

	/**
	 * Gets the paths where locales and domains can be.
	 *
	 * @return string[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getPaths(): array
	{
		return $this->paths;
	}

	/**
	 * Returns TRUE if the given locale is loaded.
	 *
	 * @param string $locale
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function isLocaleLoaded(string $locale): bool
	{
		return isset($this->locales[$locale]);
	}

	/**
	 * Loads the given locale.
	 *
	 * @param string $locale
	 * @param bool $reload
	 *
	 * @return Locale
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function loadLocale(string $locale, bool $reload = false): Locale
	{
		if ($this->isLocaleLoaded($locale))
		{
			if (!$reload)
				throw new InternationalizationException(sprintf('The locale %s is already loaded.', $locale), InternationalizationException::ERR_LOCALE_ALREADY_LOADED);

			$this->unloadLocale($locale);
		}

		$localePath = null;

		foreach ($this->paths as $path)
		{
			$path = $path . '/' . $locale . '/index.json';

			if (!is_file($path))
				continue;

			$localePath = $path;
		}

		if ($localePath === null)
			throw new InternationalizationException(sprintf('The locale %s does not exist.', $locale), InternationalizationException::ERR_LOCALE_NOT_FOUND);

		return $this->locales[$locale] = new Locale($this, $localePath);
	}

	/**
	 * Sets the current locale.
	 *
	 * @param string|null $locale
	 *
	 * @return Locale
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function setLocale(?string $locale): Locale
	{
		$this->current = $this->getLocale($locale);

		return $this->current;
	}

	/**
	 * Translates the given key in the given domain and applies the given arguments
	 * with it.
	 *
	 * @param string $domain
	 * @param string $key
	 * @param mixed ...$arguments
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function translate(string $domain, string $key, ...$arguments): string
	{
		if ($this->current === null)
			return "{$domain}[{$key}]";

		return $this->current->translate($domain, $key, ...$arguments) ?? "{$domain}[{$key}]";
	}

	/**
	 * Unloads the given locale.
	 *
	 * @param string $locale
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function unloadLocale(string $locale): void
	{
		if (!$this->isLocaleLoaded($locale))
			return;

		unset($this->locales[$locale]);
	}

}
