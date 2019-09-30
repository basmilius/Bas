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

/**
 * Class RuleValidate
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Contract\Rule
 * @since 1.6.0
 */
final class RuleValidate extends AbstractRule
{

	/**
	 * @var callable
	 */
	private $predicate;

	/**
	 * RuleValidate constructor.
	 *
	 * @param Contract $contract
	 * @param Term     $term
	 * @param callable $predicate
	 *
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.6.0
	 */
	public function __construct(Contract $contract, Term $term, callable $predicate)
	{
		parent::__construct($contract, $term);

		$this->predicate = $predicate;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function met(&$value): bool
	{
		$predicate = $this->predicate;

		if ($predicate($value))
			return true;

		return $this->breach('The given value was not accepted by the predicate.');
	}

}
