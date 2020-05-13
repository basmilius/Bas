<?php
/**
 * Copyright (c) 2017 - 2020 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Latte Framework package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Database\Cast;

use Columba\Database\Model\Model;
use function json_decode;
use function json_encode;
use const JSON_BIGINT_AS_STRING;
use const JSON_HEX_AMP;
use const JSON_HEX_APOS;
use const JSON_HEX_QUOT;
use const JSON_HEX_TAG;

/**
 * Class Json
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Cast
 * @since 1.6.0
 */
class Json implements ICast
{

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function get(Model $model, string $key, $value, array $data)
	{
		if ($value === null)
			return null;

		return json_decode($value, true);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function set(Model $model, string $key, $value, array $data)
	{
		if ($value === null)
			return null;

		return json_encode($value, JSON_BIGINT_AS_STRING | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG);
	}

}
