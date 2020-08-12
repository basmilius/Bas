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

namespace Columba\Facade;

use JsonSerializable;

/**
 * Interface IJson
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Facade
 * @since 1.4.0
 */
interface IJson extends JsonSerializable
{

	/**
	 * Returns which data should be available in json.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function jsonSerialize(): array;

}
