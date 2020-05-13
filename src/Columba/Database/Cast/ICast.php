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
 * Interface ICast
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Cast
 * @since 1.6.0
 */
interface ICast
{

	/**
	 * Deserializes the data before using it in the model.
	 *
	 * @param Model $model
	 * @param string $key
	 * @param mixed $value
	 * @param array $data
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function get(Model $model, string $key, $value, array $data);

	/**
	 * Serializes the data before storing it back in the database.
	 *
	 * @param Model $model
	 * @param string $key
	 * @param mixed $value
	 * @param array $data
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function set(Model $model, string $key, $value, array $data);

}
