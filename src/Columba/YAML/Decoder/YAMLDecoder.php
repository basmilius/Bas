<?php
declare(strict_types=1);

namespace Columba\YAML\Decoder;

use Columba\YAML\YAMLException;

/**
 * Class YAMLDecoder
 *
 * @author Bas Milius <bas@ideemedia.nl>
 * @package Columba\YAML\Decoder
 * @since 1.4.0
 */
final class YAMLDecoder
{

	/**
	 * @var string|null
	 */
	private $yaml;

	/**
	 * YAMLDecoder constructor.
	 *
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.4.0
	 */
	public function __construct()
	{
		$this->yaml = null;
	}

	/**
	 * Loads a YAML document from file.
	 *
	 * @param string $fileName
	 *
	 * @throws YAMLException
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.4.0
	 */
	public final function loadFile(string $fileName): void
	{
		if (!is_readable($fileName))
			throw new YAMLException('Could not find or read yaml file: ' . $fileName);

		$this->yaml = file_get_contents($fileName);
	}

	/**
	 * Loads a YAML document from string.
	 *
	 * @param string $yaml
	 *
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.4.0
	 */
	public final function loadString(string $yaml): void
	{
		$this->yaml = $yaml;
	}

	public final function decode(): array
	{

		pre_die($this);

		return [];
	}

}
