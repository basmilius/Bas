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

namespace Columba\Router\Response;

use Columba\Router\Context;
use function is_array;
use function json_encode;
use const JSON_BIGINT_AS_STRING;
use const JSON_HEX_AMP;
use const JSON_HEX_APOS;
use const JSON_HEX_QUOT;
use const JSON_HEX_TAG;

/**
 * Class JsonResponse
 *
 * @package Columba\Router\Response
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.0
 */
class JsonResponse extends AbstractResponse
{

	public const DEFAULT_OPTIONS = JSON_BIGINT_AS_STRING | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG;

	private int $options;
	private bool $withDefaults;

	/**
	 * JsonResponse constructor.
	 *
	 * @param bool $withDefaults
	 * @param int  $options
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(bool $withDefaults = true, int $options = self::DEFAULT_OPTIONS)
	{
		$this->withDefaults = $withDefaults;
		$this->options = $options;

		$this->addHeader('Access-Control-Allow-Headers', '*');
		$this->addHeader('Access-Control-Allow-Methods', 'GET, PUT, PATCH, DELETE, POST, OPTIONS');
		$this->addHeader('Access-Control-Allow-Origin', '*');
		$this->addHeader('Content-Type', 'application/json; charset=utf-8');
		$this->addHeader('X-Content-Type-Options', 'nosniff');
		$this->addHeader('X-Frame-Options', 'deny');
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected function respond(Context $context, $value): string
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

		return json_encode($result, $this->options);
	}

}
