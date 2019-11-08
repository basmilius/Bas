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

namespace Columba\Contract;

use function array_merge;

/**
 * Class Contract
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Contract
 * @since 1.6.0
 */
class Contract
{

	/**
	 * @var array
	 */
	protected $errors = [];

	/**
	 * @var Term[]
	 */
	protected $terms = [];

	/**
	 * @var bool
	 */
	private $quickBreach = false;

	/**
	 * Terms have been violated, create an error here.
	 *
	 * @param Term   $term
	 * @param int    $reason
	 * @param string $message
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function breach(Term $term, int $reason, string $message = ''): bool
	{
		$this->errors[] = [$term->getName(), $reason, $message];

		return false;
	}

	/**
	 * Merges errors from another contract.
	 *
	 * @param Contract $other
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @internal
	 */
	public function mergeErrors(Contract $other): void
	{
		$this->errors = array_merge($this->errors, $other->errors);
	}

	/**
	 * Checks if the given data meets the contract terms.
	 *
	 * @param array $data
	 *
	 * @return bool
	 * @throws ContractBreachException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function met(array &$data): bool
	{
		$this->errors = [];

		$breach = false;

		foreach ($this->terms as $term)
		{
			if ($this->quickBreach && $breach)
				break;

			$termName = $term->getName();

			if (!isset($data[$termName]) && !$term->isOptional())
			{
				$this->breach($term, ContractBreachException::ERR_VALUE_REQUIRED, 'Value is required.');
				$breach = $breach || true;
				continue;
			}

			$value = $data[$termName] ?? null;

			if (!$term->met($value))
			{
				$breach = $breach || true;
				continue;
			}

			$data[$termName] = $value;
		}

		return !$breach;
	}

	/**
	 * Checks if the given data meet the contract terms otherwise throw an exception.
	 *
	 * @param array $data
	 *
	 * @throws ContractBreachException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function metOrThrow(array &$data): void
	{
		if (!$this->met($data))
			throw new ContractBreachException('Data did not met contract terms.');
	}

	/**
	 * Bail on the first error instead of continuing.
	 *
	 * @param bool $is
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function quickBreach(bool $is = true): void
	{
		$this->quickBreach = $is;
	}

	/**
	 * Adds a new contract term.
	 *
	 * @param string $name
	 *
	 * @return Term
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function term(string $name): Term
	{
		return $this->terms[] = new Term($this, $name);
	}

}
