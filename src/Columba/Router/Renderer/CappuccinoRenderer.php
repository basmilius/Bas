<?php
/**
 * Copyright © 2018 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Latte Framework package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Router\Renderer;

use Cappuccino\Cappuccino;
use Cappuccino\Extension\ExtensionInterface;
use Cappuccino\Loader\FilesystemLoader;

/**
 * Class CappuccinoRenderer
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router\Renderer
 * @since 1.2.0
 */
class CappuccinoRenderer extends AbstractRenderer
{

	/**
	 * @var Cappuccino
	 */
	private $cappuccino;

	/**
	 * @var FilesystemLoader
	 */
	private $loader;

	/**
	 * @var array
	 */
	private $options;

	/**
	 * CappuccinoRenderer constructor.
	 *
	 * @param array $options
	 *
	 * @throws \Cappuccino\Error\LoaderError
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public function __construct (array $options = [])
	{
		$defaultOptions = [
			'debug' => false
		];
		$options = array_merge($defaultOptions, $options);

		$this->loader = new FilesystemLoader([]);
		$this->options = $options;

		$this->cappuccino = new Cappuccino($this->loader, $options);
	}

	/**
	 * Adds a Cappuccino extension.
	 *
	 * @param ExtensionInterface $extension
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function addExtension (ExtensionInterface $extension): void
	{
		$this->cappuccino->addExtension($extension);
	}

	/**
	 * Adds a global variable.
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function addGlobal (string $name, $value): void
	{
		$this->cappuccino->addGlobal($name, $value);
	}

	/**
	 * Adds a filesystem path.
	 *
	 * @param string       $path
	 * @param string |null $namespace
	 *
	 * @throws \Cappuccino\Error\LoaderError
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function addPath (string $path, ?string $namespace = null): void
	{
		$this->loader->addPath($path, $namespace ?? FilesystemLoader::MAIN_NAMESPACE);
	}

	/**
	 * {@inheritdoc}
	 * @throws \Cappuccino\Error\LoaderError
	 * @throws \Cappuccino\Error\RuntimeError
	 * @throws \Cappuccino\Error\SyntaxError
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public function render (string $template, array $context = []): string
	{
		if (substr($template, -6) !== '.cappy')
			$template .= '.cappy';

		return $this->cappuccino->render($template, $context);
	}

	/**
	 * Gets the {@see Cappuccino} instance.
	 *
	 * @return Cappuccino
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getCappuccino (): ?Cappuccino
	{
		return $this->cappuccino;
	}

}
