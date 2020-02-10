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

use Columba\Contract\Contract;
use Columba\Contract\Term;

/**
 * Class RuleSubcontract
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Contract\Rule
 * @since 1.6.0
 */
final class RuleSubcontract extends AbstractRule
{

	private Contract $other;

	/**
	 * RuleSubcontract constructor.
	 *
	 * @param Contract $contract
	 * @param Term     $term
	 * @param Contract $other
	 *
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.6.0
	 */
	public function __construct(Contract $contract, Term $term, Contract $other)
	{
		parent::__construct($contract, $term);

		$this->other = $other;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function met(&$value): bool
	{
		if ($this->other->met($value))
			return true;

		$this->contract->mergeErrors($this->other);

		return $this->breach('The given value did not met subcontract terms.');
	}

}
