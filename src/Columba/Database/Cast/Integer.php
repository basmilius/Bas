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

/**
 * Class Integer
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Cast
 * @since 1.6.0
 */
class Integer implements ICast
{

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function decode(Model $model, string $key, $value, array $data): int
	{
		return (int)$value;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function encode(Model $model, string $key, $value, array $data)
	{
		return $value;
	}

}
