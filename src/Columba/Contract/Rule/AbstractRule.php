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
use Columba\Contract\ContractBreachException;
use Columba\Contract\Term;

/**
 * Class AbstractRule
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Contract\Rule
 * @since 1.6.0
 */
abstract class AbstractRule
{

	protected Contract $contract;
	protected Term $term;

	/**
	 * AbstractRule constructor.
	 *
	 * @param Contract $contract
	 * @param Term     $term
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(Contract $contract, Term $term)
	{
		$this->contract = $contract;
		$this->term = $term;
	}

	/**
	 * Adds an error to the contract result because the given value failed on this rule.
	 *
	 * @param string $message
	 * @param mixed  $value
	 * @param int    $reason
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected final function breach(string $message, $value = null, int $reason = ContractBreachException::ERR_RULE_NOT_SATISFIABLE): bool
	{
		return $this->contract->breach($this->term, $reason, $message, $value);
	}

	/**
	 * Returns TRUE if the given value mets the criteria for this rule.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 * @throws ContractBreachException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public abstract function met(&$value): bool;

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function __debugInfo(): ?array
	{
		return null;
	}

}
