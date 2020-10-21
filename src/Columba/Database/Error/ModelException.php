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
 * Class ModelException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Error
 * @since 1.6.0
 */
class ModelException extends DatabaseException
{

	public const ERR_IMMUTABLE = 1;
	public const ERR_NOT_FOUND = 2;
	public const ERR_BAD_METHOD_CALL = 4;
	public const ERR_CASTER_NOT_FOUND = 8;
	public const ERR_RELATION_NOT_FOUND = 16;

}
