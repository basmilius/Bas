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

namespace Columba\Image\GIF;

use Columba\Error\ColumbaException;

/**
 * Class GIFException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Image\GIF
 * @since 1.6.0
 */
class GIFException extends ColumbaException
{

	public const ERR_NOT_IMPLEMENTED = 1;
	public const ERR_INVALID_FRAME = 2;
	public const ERR_UNEXPECTED = 4;

}
