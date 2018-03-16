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

namespace Columba;

/**
 * Class Autoloader
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba
 * @since 1.0.0
 */
final class Autoloader
{

	/**
	 * @var mixed[][]
	 */
	private $definitions;

	/**
	 * Autoloader constructor.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct ()
	{
		$this->definitions = [];
	}

	/**
	 * Adds a directory to {@see Autoloader}.
	 *
	 * @param string      $directory
	 * @param string|null $namespace
	 * @param bool        $isVirtualNamespace
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function addDirectory (string $directory, ?string $namespace = null, bool $isVirtualNamespace = false): void
	{
		$this->definitions[] = [realpath($directory), $namespace, $isVirtualNamespace];
	}

	/**
	 * Registers our {@see Autoloader}.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function register (): void
	{
		spl_autoload_register([$this, 'onRequestObject'], true, true);
	}

	/**
	 * Removes our {@see Autoloader}.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function unregister (): void
	{
		spl_autoload_unregister([$this, 'onRequestObject']);
	}

	/**
	 * Invoked when an object is requested.
	 *
	 * @param string $object
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function onRequestObject (string $object): bool
	{
		$didAutoload = false;
		$object = str_replace('_', '\\', $object);

		foreach ($this->definitions as $definition)
			if ($didAutoload)
				break;
			else if (($file = $this->file($definition[0], $definition[1], $definition[2], $object)) !== null)
				$didAutoload = $this->require($file);

		return $didAutoload;
	}

	/**
	 * Returns the file path or NULL if it's not a real path.
	 *
	 * @param string      $directory
	 * @param string|null $namespace
	 * @param bool        $isVirtualNamespace
	 * @param string      $object
	 *
	 * @return string|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function file (string $directory, ?string $namespace, bool $isVirtualNamespace, string $object): ?string
	{
		if ($namespace !== null && substr($object, 0, strlen($namespace)) !== $namespace)
			return null;

		if ($isVirtualNamespace && $namespace !== null)
			$object = str_replace($namespace, '', $object);

		$object = str_replace('\\', DIRECTORY_SEPARATOR, $object);

		$file = realpath($directory . DIRECTORY_SEPARATOR . $object . '.php');

		return $file ? $file : null;
	}

	/**
	 * Requires a file.
	 *
	 * @param string $file
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function require (string $file): bool
	{
		if (!is_file($file))
			return false;

		/** @noinspection PhpIncludeInspection */
		require_once $file;

		return true;
	}

}
