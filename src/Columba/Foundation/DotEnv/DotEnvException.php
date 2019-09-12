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

namespace Columba\Foundation\DotEnv;

use Columba\Error\ColumbaException;

/**
 * Class DotEnvException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Foundation\DotEnv
 * @since 1.6.0
 */
final class DotEnvException extends ColumbaException
{

	public const ERR_UNKNOWN = 0;
	public const ERR_FILE_NOT_READABLE = 1;
	public const ERR_SYNTAX_ERROR = 2;
	public const ERR_IMMUTABLE = 4;

}
