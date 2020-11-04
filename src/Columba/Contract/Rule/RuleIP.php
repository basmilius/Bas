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

namespace Columba\Contract\Rule;

use function filter_var;

/**
 * Class RuleIP
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Contract\Rule
 * @since 1.6.0
 */
final class RuleIP extends AbstractRule
{

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 *
	 * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
	 */
	public final function met(&$value): bool
	{
		if (!filter_var($value, FILTER_VALIDATE_IP))
			return $this->breach('The given value is not an ip address.', $value);

		return true;
	}

}
