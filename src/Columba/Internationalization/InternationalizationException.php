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

namespace Columba\Internationalization;

use Columba\Error\ColumbaException;

/**
 * Class InternationalizationException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Internationalization
 * @since 1.6.0
 */
class InternationalizationException extends ColumbaException
{

	public const ERR_INVALID_PATH = 1;
	public const ERR_LOCALE_NOT_FOUND = 2;
	public const ERR_LOCALE_ALREADY_LOADED = 4;
	public const ERR_DOMAIN_NOT_FOUND = 8;
	public const ERR_DOMAIN_ALREADY_LOADED = 16;

}
