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
use function count;
use function is_countable;

/**
 * Class RuleCount
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Contract\Rule
 * @since 1.6.0
 */
final class RuleCount extends AbstractRule
{

	private int $count;

	/**
	 * RuleCount constructor.
	 *
	 * @param Contract $contract
	 * @param Term     $term
	 * @param int      $count
	 *
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.6.0
	 */
	public function __construct(Contract $contract, Term $term, int $count)
	{
		parent::__construct($contract, $term);

		$this->count = $count;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function met(&$value): bool
	{
		if (!is_countable($value))
			return $this->breach('The given value is not countable.');

		if (count($value) !== $this->count)
			return $this->breach('The given value does not have a count of ' . $this->count);

		return true;
	}

}
