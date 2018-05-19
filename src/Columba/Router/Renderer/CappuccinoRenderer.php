<?php
declare(strict_types=1);

namespace Columba\Router\Renderer;

use Cappuccino\Cappuccino;
use Cappuccino\Error\LoaderError;
use Cappuccino\Error\RuntimeError;
use Cappuccino\Error\SyntaxError;
use Cappuccino\Extension\ExtensionInterface;
use Cappuccino\Loader\FilesystemLoader;
use Cappuccino\Loader\LoaderInterface;

/**
 * Class CappuccinoRenderer
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router\Renderer
 * @since 1.3.0
 */
class CappuccinoRenderer extends AbstractRenderer
{

	private const DEFAULT_OPTIONS = [
		'debug' => false
	];

	/**
	 * @var Cappuccino
	 */
	protected $cappuccino;

	/**
	 * @var LoaderInterface
	 */
	protected $loader;

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * CappuccinoRenderer constructor.
	 *
	 * @param array                $options
	 * @param LoaderInterface|null $loader
	 *
	 * @throws \Cappuccino\Error\LoaderError
	 * @throws \Cappuccino\Error\RuntimeError
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(array $options = [], ?LoaderInterface $loader = null)
	{
		$options = array_merge(self::DEFAULT_OPTIONS, $options);

		$this->loader = $loader ?? new FilesystemLoader([]);
		$this->options = $options;

		$this->cappuccino = new Cappuccino($this->loader, $options);
	}

	/**
	 * Adds an extension.
	 *
	 * @param ExtensionInterface $extension
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function addExtension(ExtensionInterface $extension): void
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
	 * @since 1.3.0
	 */
	public final function addGlobal(string $name, $value): void
	{
		$this->cappuccino->addGlobal($name, $value);
	}

	/**
	 * Adds a view path.
	 *
	 * @param string $path
	 * @param string $namespace
	 *
	 * @throws LoaderError
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function addPath(string $path, string $namespace = FilesystemLoader::MAIN_NAMESPACE): void
	{
		$this->loader->addPath($path, $namespace);
	}

	/**
	 * Gets the {@see Cappuccino} instance.
	 *
	 * @return Cappuccino
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getCappuccino(): Cappuccino
	{
		return $this->cappuccino;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function render(string $template, array $context = []): string
	{
		try
		{
			return $this->cappuccino->render($template, $context);
		}
		catch (LoaderError|RuntimeError|SyntaxError $err)
		{
			throw $this->error($err);
		}
	}

}
