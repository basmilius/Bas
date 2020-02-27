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

use Columba\Facade\Debuggable;
use function array_map;
use function array_merge;
use function array_unique;
use function basename;
use function dirname;
use function file_get_contents;
use function glob;
use function is_file;
use function json_decode;

/**
 * Class Internationalization
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Internationalization
 * @since 1.6.0
 */
class Locale implements Debuggable
{

	protected Internationalization $i18n;
	protected string $directory;
	protected string $path;

	protected string $id;
	protected string $name;
	protected string $nameNative;
	protected ?self $parent;

	protected array $domains = [];

	/**
	 * Internationalization constructor.
	 *
	 * @param Internationalization $i18n
	 * @param string               $path
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(Internationalization $i18n, string $path)
	{
		$this->i18n = $i18n;
		$this->directory = dirname($path);
		$this->path = $path;

		$jsonData = json_decode(file_get_contents($path), true);

		$this->id = $jsonData['id'];
		$this->name = $jsonData['name'];
		$this->nameNative = $jsonData['name_native'] ?? $jsonData['name'];
		$this->parent = isset($jsonData['parent']) ? $i18n->getLocale($jsonData['parent']) : null;
	}

	/**
	 * Gets the ID of the locale.
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
	 * Gets the English name of the locale.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Gets the native name of the locale.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getNameNative(): string
	{
		return $this->nameNative;
	}

	/**
	 * Gets the parent locale.
	 *
	 * @return Locale|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getParent(): ?self
	{
		return $this->parent;
	}

	/**
	 * Gets the label of the locale with both name and nameNative.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getLabel(): string
	{
		return "{$this->nameNative} ({$this->name})";
	}

	/**
	 * Gets all available domains from the locale.
	 *
	 * @return string[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getAvailableDomains(): array
	{
		$domains = $this->parent !== null ? $this->parent->getAvailableDomains() : [];

		$files = glob("{$this->directory}/domain/*.json");
		$files = array_map(fn(string $file) => basename($file, '.json'), $files);

		return array_unique(array_merge($domains, $files));
	}

	/**
	 * Gets a domain or if laods one if it isn't already.
	 *
	 * @param string $domain
	 *
	 * @return Domain
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getDomain(string $domain): Domain
	{
		if ($this->isDomainLoaded($domain))
			return $this->domains[$domain];

		return $this->loadDomain($domain);
	}

	/**
	 * Returns TRUE if the given domain is loaded.
	 *
	 * @param string $domain
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function isDomainLoaded(string $domain): bool
	{
		return isset($this->domains[$domain]);
	}

	/**
	 * Loads the given domain.
	 *
	 * @param string $domain
	 * @param bool   $silent
	 *
	 * @return Domain|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function loadDomain(string $domain, bool $silent = false): ?Domain
	{
		if ($this->isDomainLoaded($domain))
		{
			if (!$silent)
				throw new InternationalizationException(sprintf('The domain %s is already loaded.', $domain), InternationalizationException::ERR_DOMAIN_ALREADY_LOADED);

			return null;
		}

		$parentDomain = null;

		if ($this->parent !== null)
			$parentDomain = $this->parent->loadDomain($domain, true);

		$domainPath = null;

		foreach ($this->i18n->getPaths() as $path)
		{
			$path = "{$path}/{$this->id}/domain/{$domain}.json";

			if (!is_file($path))
				continue;

			$domainPath = $path;
			break;
		}

		if ($domainPath === null)
		{
			if ($parentDomain === null)
			{
				if ($silent)
					return null;

				throw new InternationalizationException(sprintf('The domain %s does not exist in the %s locale.', $domain, $this->id), InternationalizationException::ERR_DOMAIN_NOT_FOUND);
			}

			return $this->domains[$domain] = $parentDomain;
		}

		return $this->domains[$domain] = new Domain($this->i18n, $this, $domainPath);
	}

	/**
	 * Translates the given key using the given domain with the given arguments.
	 *
	 * @param string $domain
	 * @param string $key
	 * @param mixed  ...$arguments
	 *
	 * @return string|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function translate(string $domain, string $key, ...$arguments): ?string
	{
		$translation = $this->getDomain($domain)->translate($key, ...$arguments);

		if ($translation === null && $this->parent !== null)
			$translation = $this->parent->translate($domain, $key, ...$arguments);

		return $translation;
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
			'name' => $this->getLabel(),
			'parent' => $this->parent,
			'domains' => $this->domains
		];
	}

}
