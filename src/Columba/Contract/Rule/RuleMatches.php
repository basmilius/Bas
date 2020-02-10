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
use function is_string;
use function preg_match;
use function sprintf;

/**
 * Class RuleMatches
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Contract\Rule
 * @since 1.6.0
 */
final class RuleMatches extends AbstractRule
{

	private string $pattern;

	/**
	 * RuleMatches constructor.
	 *
	 * @param Contract $contract
	 * @param Term     $term
	 * @param string   $pattern
	 *
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.6.0
	 */
	public function __construct(Contract $contract, Term $term, string $pattern)
	{
		parent::__construct($contract, $term);

		$this->pattern = $pattern;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function met(&$value): bool
	{
		if (!is_string($value))
			return $this->breach('The given value is not a string.', $value);

		if (!preg_match($this->pattern, $value))
			return $this->breach(sprintf('The given value does not match "%s".', $this->pattern), $value);

		return true;
	}

}
