<?php
/**
 * Copyright (c) 2019 - 2020 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Util;

use JsonSerializable;
use SimpleXMLElement;
use function dom_import_simplexml;
use function htmlspecialchars;
use function is_array;
use function is_bool;
use function is_int;
use function is_string;
use function strip_tags;
use function substr;

/**
 * Class XmlUtil
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Util
 * @since 1.3.0
 */
final class XmlUtil
{

	/**
	 * Converts an array to an XML object.
	 *
	 * @param array $array
	 * @param SimpleXMLElement|null $xml
	 * @param string|null $parentName
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public static function arrayToXml(array $array, SimpleXMLElement &$xml = null, ?string $parentName = null): void
	{
		if ($xml === null)
			$xml = new SimpleXMLElement('<root></root>');

		foreach ($array as $key => $value)
		{
			if (is_int($key))
				$key = $parentName !== null ? self::generateSingularName($parentName) : 'item';

			if ($key[0] === '@')
				$key = 'entity:' . substr($key, 1);

			if ($value instanceof JsonSerializable)
				$value = $value->jsonSerialize();

			if (is_array($value))
			{
				$item = $xml->addChild($key);
				self::arrayToXml($value, $item, $key);
			}
			else if (is_bool($value))
			{
				$xml->addChild($key, $value ? '1' : '0');
			}
			else if (is_string($value) && $value !== strip_tags($value))
			{
				$xml->{$key} = null;
				$node = dom_import_simplexml($xml->{$key});
				$no = $node->ownerDocument;
				$node->appendChild($no->createCDATASection($value));
			}
			else
			{
				$xml->addChild($key, htmlspecialchars((string)$value));
			}
		}
	}

	/**
	 * Tries to converts a plural name to a singular name.
	 *
	 * @param string $name
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	private static function generateSingularName(string $name): string
	{
		if (substr($name, -1) === 's')
			return substr($name, 0, -1);
		else if (substr($name, -5) === '_list')
			return substr($name, 0, -5);
		else
			return 'item';
	}

}
