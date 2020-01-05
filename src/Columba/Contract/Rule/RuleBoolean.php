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

use function array_merge;
use function in_array;
use function is_bool;

/**
 * Class RuleBoolean
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Contract\Rule
 * @since 1.6.0
 */
final class RuleBoolean extends AbstractRule
{

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function met(&$value): bool
	{
		if (is_bool($value))
			return true;

		$trueValues = ['1', 'on', 'true', 'yes'];
		$falseValues = ['0', 'off', 'false', 'no'];
		$allValues = array_merge($trueValues, $falseValues);

		if (!in_array($value, $allValues))
			return $this->breach('The given value is not a boolean value.', $value);

		$value = in_array($value, $trueValues);

		return true;
	}

}
