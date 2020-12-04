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

namespace Columba\Router\Response;

use Columba\Router\Context;
use Columba\Util\XmlUtil;
use DOMDocument;
use SimpleXMLElement;
use function is_array;

/**
 * Class XmlResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router\Response
 * @since 1.3.0
 */
class XmlResponse extends AbstractResponse
{

	public const ROOT = '<response></response>';

	private bool $prettyPrint;
	private string $root;
	private bool $withDefaults;

	/**
	 * XmlResponse constructor.
	 *
	 * @param bool $withDefaults
	 * @param bool $prettyPrint
	 * @param string $root
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(bool $withDefaults = true, bool $prettyPrint = false, string $root = self::ROOT)
	{
		$this->prettyPrint = $prettyPrint;
		$this->root = $root;
		$this->withDefaults = $withDefaults;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected function respond(Context $context, $value): string
	{
		$this->addHeader('Content-Type', 'text/xml; charset=utf-8');

		if ($value instanceof SimpleXMLElement)
		{
			$xml = $value;
		}
		else
		{
			if ($this->withDefaults)
			{
				$header = [
					'execution_time' => $context->getResolutionTime(),
					'response_code' => $context->getResponseCode()
				];
				$result = ['header' => $header];
				$success = true;

				if (is_array($value))
				{
					if (isset($value['error']))
						$result['error'] = $value['error'];
					else
						$result['data'] = $value;
				}
				else
				{
					$result['data'] = $value;
				}

				$result['success'] = $success;
			}
			else
			{
				$result = $value;
			}

			$xml = new SimpleXMLElement($this->root);

			XmlUtil::arrayToXml($result, $xml);
		}

		if ($this->prettyPrint)
		{
			$doc = new DOMDocument();
			$doc->loadXML($xml->asXML());
			$doc->formatOutput = true;

			return $doc->saveXML();
		}

		return $xml->asXML();
	}

}
