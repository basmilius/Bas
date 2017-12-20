<?php
declare(strict_types=1);

namespace Bas\Plesk;

use PleskX\Api\XmlResponse;

/**
 * Class PleskApiUtil
 *
 * @author Bas Milius <bas@ideemedia.nl>
 * @package Bas\Plesk
 * @since 1.0.0
 */
final class PleskApiUtil
{

	/**
	 * Creates an dataset array.
	 *
	 * @param string[] ...$sets
	 *
	 * @return array
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.0.0
	 */
	public static function createDataSet (string ...$sets): array
	{
		$dataset = array_flip($sets);

		foreach ($dataset as $set => $_)
			$dataset[$set] = '';

		return $dataset;
	}

	/**
	 * Converts an array of properties to an assocative array.
	 *
	 * @param array  $properties
	 * @param string $nameKey
	 * @param string $valueKey
	 *
	 * @return array
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.0.0
	 */
	public static function nameValuesToArray (array $properties, string $nameKey = 'name', string $valueKey = 'value'): array
	{
		$result = [];

		foreach ($properties as [$nameKey => $name, $valueKey => $value])
		{
			if (isset($result[$name]))
			{
				if (!is_array($result[$name]))
					$result[$name] = [$result[$name]];

				$result[$name][] = $value;

				continue;
			}

			$result[$name] = $value;
		}

		return $result;
	}

	/**
	 * Converts the Plesk API Response to an assocative array.
	 *
	 * @param XmlResponse $response
	 *
	 * @return array
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.0.0
	 */
	public static function xmlResponseToArray (XmlResponse $response): array
	{
		$response = json_encode($response);
		$response = json_decode($response, true);

		return $response;
	}

}
