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

namespace Columba\Plesk;

use JsonSerializable;
use PleskX\Api\Client;
use SimpleXMLElement;

/**
 * Class PleskApiClient
 *
 * @package Columba\Plesk
 * @author Bas Milius <bas@mili.us>
 * @since 1.0.0
 */
final class PleskApiClient
{

	/**
	 * @var Client
	 */
	private $client;

	/**
	 * @var array
	 */
	private $requestOptions;

	/**
	 * PleskApiClient constructor.
	 *
	 * @param string      $hostname
	 * @param string      $usernameOrSecretKey
	 * @param string|null $password
	 * @param array       $requestOptions
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct(string $hostname, string $usernameOrSecretKey, ?string $password = null, array $requestOptions = [])
	{
		$this->client = new Client($hostname);
		$this->requestOptions = $requestOptions;

		if ($password === null)
			$this->client->setSecretKey($usernameOrSecretKey);
		else
			$this->client->setCredentials($usernameOrSecretKey, $password);
	}

	/**
	 * Performs an API request to Plesk.
	 *
	 * @param array $request
	 * @param bool  $full
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function request(array $request, bool $full = true): array
	{
		$request = $this->requestOptions + $request;

		$this->arrayToSimpleXML($request, $xml);

		return PleskApiUtil::xmlResponseToArray($this->client->request($xml, $full ? Client::RESPONSE_FULL : Client::RESPONSE_SHORT));
	}

	/**
	 * Converts an array to XML.
	 *
	 * @param array                 $arr
	 * @param SimpleXMLElement|null $xml
	 * @param string|null           $parentKey
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function arrayToSimpleXML(array $arr, ?SimpleXMLElement &$xml = null, ?string $parentKey = ''): void
	{
		if ($xml === null)
			$xml = new SimpleXMLElement('<packet/>');

		foreach ($arr as $key => $value)
		{
			$isSequential = is_int($key);

			if ($isSequential)
				$key = $parentKey;

			if ($value instanceof JsonSerializable)
				$value = $value->jsonSerialize();

			if (is_array($value))
			{
				if (isset($value[0]))
				{
					$this->arrayToSimpleXML($value, $xml, $key);
				}
				else
				{
					$item = $xml->addChild($key);
					$this->arrayToSimpleXML($value, $item, $key);
				}
			}
			else if (is_bool($value))
			{
				$xml->addChild($key, $value ? 'True' : 'False');
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
				$xml->addChild($key, htmlspecialchars(strval($value)));
			}
		}
	}

}
