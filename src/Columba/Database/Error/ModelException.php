<?php
/**
 * Copyright (c) 2017 - 2019 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Latte Framework package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Database\Error;

/**
 * Class ModelException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Error
 * @since 1.6.0
 */
class ModelException extends DatabaseException
{

	public const ERR_IMMUTABLE = 1;

}
