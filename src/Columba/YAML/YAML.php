<?php
declare(strict_types=1);

namespace Columba\YAML;

use Columba\YAML\Decoder\YAMLDecoder;

/**
 * Class YAML
 *
 * @author Bas Milius <bas@ideemedia.nl>
 * @package Columba\YAML
 * @since 1.4.0
 */
final class YAML
{

	public static function from(string $yaml): array
	{
		$decoder = new YAMLDecoder();
		$decoder->loadString($yaml);

		return $decoder->decode();
	}

	public static function fromFile(string $fileName): array
	{
		$decoder = new YAMLDecoder();
		$decoder->loadFile($fileName);

		return $decoder->decode();
	}

	public static function to(array $data): void
	{
	}

	public static function toFile(array $data, string $yamlFile): void
	{
	}

}
