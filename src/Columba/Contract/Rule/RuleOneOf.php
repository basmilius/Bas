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
use function in_array;

/**
 * Class RuleOneOf
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Contract\Rule
 * @since 1.6.0
 */
final class RuleOneOf extends AbstractRule
{

	/**
	 * @var array
	 */
	private $values;

	/**
	 * RuleOneOf constructor.
	 *
	 * @param Contract $contract
	 * @param Term     $term
	 * @param array    $values
	 *
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.6.0
	 */
	public function __construct(Contract $contract, Term $term, array $values)
	{
		parent::__construct($contract, $term);

		$this->values = $values;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function met(&$value): bool
	{
		if (!in_array($value, $this->values))
			return $this->breach('The given value was not present in the allowed values.');

		return true;
	}

}
