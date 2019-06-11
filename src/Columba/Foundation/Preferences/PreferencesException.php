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

namespace Columba\Foundation\Preferences;

use Columba\Error\ColumbaException;

/**
 * Class PreferencesException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Foundation\Preferences
 * @since 1.6.0
 */
final class PreferencesException extends ColumbaException
{

	public const ERR_INVALID_ARGUMENT = 1;

}
