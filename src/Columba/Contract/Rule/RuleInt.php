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

use function is_int;
use function is_numeric;

/**
 * Class RuleInt
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Contract\Rule
 * @since 1.6.0
 */
final class RuleInt extends AbstractRule
{

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function met(&$value): bool
	{
		if (is_int($value))
			return true;

		if (!is_numeric($value))
			return $this->breach('The given value is not numeric.', $value);

		if ((int)$value != (float)$value)
			return $this->breach('The given value is not an integer.', $value);

		$value = (int)$value;

		return true;
	}

}
