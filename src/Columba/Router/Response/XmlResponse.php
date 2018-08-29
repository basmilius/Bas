<?php
declare(strict_types=1);

namespace Columba\Router\Response;

use Columba\Util\XmlUtil;
use SimpleXMLElement;

/**
 * Class XmlResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router\Response
 * @since 1.3.0
 */
class XmlResponse extends AbstractResponse
{

	public const ROOT = '<response/>';

	/**
	 * @var bool
	 */
	private $prettyPrint;

	/**
	 * @var string
	 */
	private $root;

	/**
	 * @var bool
	 */
	private $withDefaults;

	/**
	 * XmlResponse constructor.
	 *
	 * @param bool   $withDefaults
	 * @param bool   $prettyPrint
	 * @param string $root
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(bool $withDefaults = true, bool $prettyPrint = false, string $root = self::ROOT)
	{
		parent::__construct();

		$this->prettyPrint = $prettyPrint;
		$this->root = $root;
		$this->withDefaults = $withDefaults;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected function respond($value): string
	{
		$this->addHeader('Content-Type', 'text/xml; charset=utf-8');

		if ($this->withDefaults)
		{
			$header = [
				'execution_time' => 0.3,
				'response_code' => $this->getResponseCode()
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

		if ($this->prettyPrint)
		{
			$doc = new \DOMDocument();
			$doc->loadXML($xml->asXML());
			$doc->formatOutput = true;

			return $doc->saveXML();
		}

		return $xml->asXML();
	}

}
