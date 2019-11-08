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

namespace Columba\Contract\Rule;

use const JSON_ERROR_NONE;
use function is_string;
use function json_decode;
use function json_last_error;

/**
 * Class RuleJson
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Contract\Rule
 * @since 1.6.0
 */
final class RuleJson extends AbstractRule
{

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function met(&$value): bool
	{
		if (!is_string($value))
			return $this->breach('The given value is not a JSON-string.');

		if ($value[0] !== '{' && $value[0] !== '[')
			return $this->breach('The given value is not a JSON-string.');

		json_decode($value, true);

		if (json_last_error() !== JSON_ERROR_NONE)
			return $this->breach('The given value is not a valid JSON-string.');

		return true;
	}

}
