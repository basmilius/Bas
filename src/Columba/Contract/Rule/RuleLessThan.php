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
use function is_numeric;
use function sprintf;

/**
 * Class RuleLessThan
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Contract\Rule
 * @since 1.6.0
 */
final class RuleLessThan extends AbstractRule
{

	/** @var float|int */
	private $value;

	/**
	 * RuleLessThan constructor.
	 *
	 * @param Contract  $contract
	 * @param Term      $term
	 * @param float|int $value
	 *
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.6.0
	 */
	public function __construct(Contract $contract, Term $term, $value)
	{
		parent::__construct($contract, $term);

		$this->value = $value;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function met(&$value): bool
	{
		if (!is_numeric($value))
			return $this->breach('The given value is not numeric.', $value);

		if ($this->value <= $value)
			return $this->breach(sprintf('The given value not less than %g', $this->value), $value);

		return true;
	}

}
