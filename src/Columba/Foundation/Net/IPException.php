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

namespace Columba\Foundation\Net;

use Columba\Error\ColumbaException;

/**
 * Class IPException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Foundation\Net
 * @since 1.6.0
 */
final class IPException extends ColumbaException
{

	public const ERR_INVALID_IP = 1;

}
