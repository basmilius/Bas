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
use function floatval;
use function is_float;
use function is_int;
use function is_numeric;
use function sprintf;

/**
 * Class RuleBetween
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Contract\Rule
 * @since 1.6.0
 */
final class RuleBetween extends AbstractRule
{

	/** @var float|int */
	private $min;

	/** @var float|int */
	private $max;

	/**
	 * RuleBetween constructor.
	 *
	 * @param Contract  $contract
	 * @param Term      $term
	 * @param float|int $min
	 * @param float|int $max
	 *
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.6.0
	 */
	public function __construct(Contract $contract, Term $term, $min, $max)
	{
		parent::__construct($contract, $term);

		$this->min = $min;
		$this->max = $max;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function met(&$value): bool
	{
		if (!is_numeric($value))
			return $this->breach('The given value is not numeric.');

		if (!is_float($value) && !is_int($value))
			$value = floatval($value);

		if ($value >= $this->min && $value <= $this->max)
			return true;

		return $this->breach(sprintf('The given value is not between %g and %g', $this->min, $this->max));
	}

}
