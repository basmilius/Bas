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

namespace Columba\Database\Error;

/**
 * Class QueryException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Error
 * @since 1.6.0
 */
class QueryException extends DatabaseException
{

	public const ERR_MISSING_COLUMNS = 1;
	public const ERR_NO_TRANSACTION = 2;
	public const ERR_INVALID_MODEL = 4;
	public const ERR_NOT_IMPLEMENTED = 8;

}
