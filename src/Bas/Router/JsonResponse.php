<?php
/**
 * This file is part of the Bas package.
 *
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bas\Router;

use Bas\Util\ExecutionTime;

/**
 * Class JsonResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Router
 * @since 1.0.0
 */
final class JsonResponse extends AbstractResponse
{

	/**
	 * JsonResponse constructor.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct ()
	{
		ExecutionTime::start(self::class);
	}

	/**
	 * Prints {@see $data} to the output buffer.
	 *
	 * @param mixed     $data
	 * @param bool|null $success
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function print ($data, ?bool $success = null): void
	{
		header('Access-Control-Allow-Headers: Authorization');
		header('Access-Control-Allow-Methods: GET PUT PATCH DELETE POST OPTIONS');
		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json; charset=utf-8');
		header('X-Author: bas@mili.us');
		header('X-Content-Type-Options: nosniff');
		header('X-Frame-Options: deny');
		header('X-Provider: ideemedia.nl');

		$response = [];

		if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS')
		{
			http_response_code(200);
		}
		else
		{
			$executionTime = ExecutionTime::stop(self::class);

			$response['api']['execution_time'] = $executionTime;
			$response['api']['response_code'] = http_response_code();

			if (is_array($data) && isset($data['error']))
				$response['error'] = $data['error'];
			else
				$response['data'] = $data;

			$response['success'] = $success ?? (!is_array($data) || !isset($data['error']));

			echo json_encode($response, self::jsonOptions());
		}

		die;
	}

	/**
	 * Gets the JSON default options.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function jsonOptions (): int
	{
		return JSON_NUMERIC_CHECK | JSON_BIGINT_AS_STRING | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_PRESERVE_ZERO_FRACTION;
	}

}
