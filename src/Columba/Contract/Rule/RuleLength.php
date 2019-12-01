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

use Columba\Contract\Contract;
use Columba\Contract\Term;
use function is_string;
use function mb_strlen;

/**
 * Class RuleLength
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Contract\Rule
 * @since 1.6.0
 */
final class RuleLength extends AbstractRule
{

	private int $length;

	/**
	 * RuleLength constructor.
	 *
	 * @param Contract $contract
	 * @param Term     $term
	 * @param int      $length
	 *
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.6.0
	 */
	public function __construct(Contract $contract, Term $term, int $length)
	{
		parent::__construct($contract, $term);

		$this->length = $length;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function met(&$value): bool
	{
		if (!is_string($value))
			return $this->breach('The given value is not a string.');

		if (mb_strlen($value) !== $this->length)
			return $this->breach('The given value does not have a length of ' . $this->length);

		return true;
	}

}
