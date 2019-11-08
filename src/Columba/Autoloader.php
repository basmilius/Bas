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

namespace Columba;

use Columba\Util\FileSystemUtil;
use Columba\Util\StringUtil;
use const DIRECTORY_SEPARATOR;
use function dirname;
use function is_dir;
use function realpath;
use function spl_autoload_call;
use function spl_autoload_register;
use function spl_autoload_unregister;
use function str_replace;
use function strlen;
use function substr;

/**
 * Class Autoloader
 *
 * @package Columba
 * @author Bas Milius <bas@mili.us>
 * @since 1.0.0
 */
final class Autoloader
{

	/**
	 * @var array
	 */
	private $definitions = [];

	/**
	 * Autoloader constructor.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function __construct()
	{
		$this->addDirectory(dirname(__DIR__));
		$this->loadFile(__DIR__ . '/Util/functions.php');
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
	public final function addDirectory(string $directory, ?string $namespace = null, bool $isVirtualNamespace = false): void
	{
		$this->definitions[] = [$directory, $namespace, $isVirtualNamespace];
	}

	/**
	 * Loads all php files from a given directory. If {@see $recursive} is TRUE
	 * subdirectories will also be loaded.
	 *
	 * @param string $dir
	 * @param bool   $recursive
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function loadDirectory(string $dir, bool $recursive = false): void
	{
		$entries = FileSystemUtil::scanDir($dir);

		foreach ($entries as $entry)
		{
			if (is_dir($entry) && $recursive)
				$this->loadDirectory($entry, true);
			else if (StringUtil::endsWith($entry, '.php'))
				$this->loadFile($entry);
		}
	}

	/**
	 * Loads a file.
	 *
	 * @param string $file
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function loadFile(string $file): void
	{
		/** @noinspection PhpIncludeInspection */
		require $file;
	}

	/**
	 * Loads a class, interface or trait manually.
	 *
	 * @param string $object
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function loadObject(string $object): void
	{
		spl_autoload_call($object);
	}

	/**
	 * Registers our {@see Autoloader}.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function register(): void
	{
		spl_autoload_register([$this, 'onRequestObject'], true, true);
	}

	/**
	 * Removes our {@see Autoloader}.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function unregister(): void
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
	private function onRequestObject(string $object): bool
	{
		$didAutoload = false;

		foreach ($this->definitions as [$directory, $namespace, $isVirtualNamespace])
		{
			$file = $this->file($directory, $namespace, $isVirtualNamespace, $object);

			if ($file === null)
				continue;

			/** @noinspection PhpIncludeInspection */
			require $file;
			return true;
		}

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
	private function file(string $directory, ?string $namespace, bool $isVirtualNamespace, string $object): ?string
	{
		if ($namespace !== null && substr($object, 0, strlen($namespace)) !== $namespace)
			return null;

		if ($isVirtualNamespace && $namespace !== null)
			$object = str_replace($namespace, '', $object);

		$object = str_replace('\\', DIRECTORY_SEPARATOR, $object);
		$file = realpath($directory . DIRECTORY_SEPARATOR . $object . '.php');

		return $file ?: null;
	}

}
