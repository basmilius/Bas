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
 * Class RuleInstanceOf
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Contract\Rule
 * @since 1.6.0
 */
final class RuleInstanceOf extends AbstractRule
{

	private string $className;

	/**
	 * RuleInstanceOf constructor.
	 *
	 * @param Contract $contract
	 * @param Term $term
	 * @param string $className
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(Contract $contract, Term $term, string $className)
	{
		parent::__construct($contract, $term);

		$this->className = $className;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 *
	 * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
	 */
	public final function met(&$value): bool
	{
		if ($value instanceof $this->className)
			return true;

		return $this->breach('The given value is not an instance of ' . $this->className);
	}

}
