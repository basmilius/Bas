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

use Closure;
use Columba\Contract\Rule\AbstractRule;
use Columba\Contract\Rule\RuleArray;
use Columba\Contract\Rule\RuleBetween;
use Columba\Contract\Rule\RuleBoolean;
use Columba\Contract\Rule\RuleCount;
use Columba\Contract\Rule\RuleEmail;
use Columba\Contract\Rule\RuleFloat;
use Columba\Contract\Rule\RuleGreaterOrEqualTo;
use Columba\Contract\Rule\RuleGreaterThan;
use Columba\Contract\Rule\RuleInstanceOf;
use Columba\Contract\Rule\RuleInt;
use Columba\Contract\Rule\RuleIP;
use Columba\Contract\Rule\RuleJson;
use Columba\Contract\Rule\RuleLength;
use Columba\Contract\Rule\RuleLessOrEqualTo;
use Columba\Contract\Rule\RuleLessThan;
use Columba\Contract\Rule\RuleMatches;
use Columba\Contract\Rule\RuleNull;
use Columba\Contract\Rule\RuleNumeric;
use Columba\Contract\Rule\RuleOneOf;
use Columba\Contract\Rule\RuleString;
use Columba\Contract\Rule\RuleSubcontract;
use Columba\Contract\Rule\RuleValidate;

/**
 * Class Term
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Contract
 * @since 1.6.0
 */
class Term
{

	protected Contract $contract;
	protected string $alias = '';
	protected string $name;
	private bool $isOptional = false;

	/**
	 * @var AbstractRule[]
	 */
	protected array $rules = [];

	/**
	 * Term constructor.
	 *
	 * @param Contract $contract
	 * @param string   $name
	 *
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.6.0
	 */
	public function __construct(Contract $contract, string $name)
	{
		$this->contract = $contract;
		$this->name = $name;
	}

	/**
	 * Adds a rule to the term.
	 *
	 * @param string $className
	 * @param mixed  ...$arguments
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function addRule(string $className, ...$arguments): self
	{
		$this->rules[] = new $className($this->contract, $this, ...$arguments);

		return $this;
	}

	/**
	 * Gets the alias name of this term.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getAlias(): string
	{
		if (!empty($this->alias))
			return $this->alias;

		return $this->name;
	}

	/**
	 * Gets the name of the term.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Returns TRUE if the term is optional.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function isOptional(): bool
	{
		return $this->isOptional;
	}

	/**
	 * Gives the term an alias.
	 *
	 * @param string $alias
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function alias(string $alias): self
	{
		$this->alias = $alias;

		return $this;
	}

	/**
	 * Returns TRUE if a value mets the rules of the term.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 * @throws ContractBreachException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @internal
	 */
	public function met(&$value): bool
	{
		foreach ($this->rules as $rule)
			if (!$rule->met($value))
				return false;

		return true;
	}

	/**
	 * Marks this term as optional.
	 *
	 * @param bool $is
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function optional(bool $is = true): self
	{
		$this->isOptional = $is;

		return $this;
	}

	/**
	 * Checks if the given value is an array.
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function array(): self
	{
		return $this->addRule(RuleArray::class);
	}

	/**
	 * Checks if the value is numeric and between the given min and max values.
	 *
	 * @param $min
	 * @param $max
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function between($min, $max): self
	{
		return $this->addRule(RuleBetween::class, $min, $max);
	}

	/**
	 * Checks if the value is a boolean.
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function boolean(): self
	{
		return $this->addRule(RuleBoolean::class);
	}

	/**
	 * Checks if the value is countable and has the given count requirement.
	 *
	 * @param int $count
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function count(int $count): self
	{
		return $this->addRule(RuleCount::class, $count);
	}

	/**
	 * Checks if the value is an e-mailaddress.
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function email(): self
	{
		return $this->addRule(RuleEmail::class);
	}

	/**
	 * Checks if the value is a float.
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function float(): self
	{
		return $this->addRule(RuleFloat::class);
	}

	/**
	 * Checks if the value is greater or equal to the given number.
	 *
	 * @param $num
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function greaterOrEqualTo($num): self
	{
		return $this->addRule(RuleGreaterOrEqualTo::class, $num);
	}

	/**
	 * Checks if the value is greater than the given number.
	 *
	 * @param float|int $num
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function greaterThan($num): self
	{
		return $this->addRule(RuleGreaterThan::class, $num);
	}

	/**
	 * Checks if the value is an instance of the given class name.
	 *
	 * @param string $className
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function instanceOf(string $className): self
	{
		return $this->addRule(RuleInstanceOf::class, $className);
	}

	/**
	 * Checks if the value is an integer.
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function int(): self
	{
		return $this->addRule(RuleInt::class);
	}

	/**
	 * Checks if the value is an ip address.
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function ip(): self
	{
		return $this->addRule(RuleIP::class);
	}

	/**
	 * Checks if the given value is a valid JSON-string.
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function json(): self
	{
		return $this->addRule(RuleJson::class);
	}

	/**
	 * Checks if the value has the given length.
	 *
	 * @param int $length
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function length(int $length): self
	{
		return $this->addRule(RuleLength::class, $length);
	}

	/**
	 * Checks if the value is less or equal to the given number.
	 *
	 * @param $num
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function lessOrEqualTo($num): self
	{
		return $this->addRule(RuleLessOrEqualTo::class, $num);
	}

	/**
	 * Checks if the value is less than the given number.
	 *
	 * @param float|int $num
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function lessThan($num): self
	{
		return $this->addRule(RuleLessThan::class, $num);
	}

	/**
	 * Checks if the value matches the given pattern.
	 *
	 * @param string $pattern
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function matches(string $pattern): self
	{
		return $this->addRule(RuleMatches::class, $pattern);
	}

	/**
	 * Checks if the value is NULL.
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function null(): self
	{
		return $this->addRule(RuleNull::class);
	}

	/**
	 * Checks if the value is numeric.
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function numeric(): self
	{
		return $this->addRule(RuleNumeric::class);
	}

	/**
	 * Checks if the value is found in the given array.
	 *
	 * @param array $values
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function oneOf(array $values): self
	{
		return $this->addRule(RuleOneOf::class, $values);
	}

	/**
	 * Checks if the value is a string.
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function string(): self
	{
		return $this->addRule(RuleString::class);
	}

	/**
	 * Checks if the value meets the requirements of the given contract.
	 *
	 * @param Contract $contract
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function subcontract(Contract $contract): self
	{
		return $this->addRule(RuleSubcontract::class, $contract);
	}

	/**
	 * Validates the value with the given predicate.
	 * Predicates should accept a reference to $value and return a bool.
	 *
	 * @param Closure $predicate
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function validate(Closure $predicate): self
	{
		return $this->addRule(RuleValidate::class, $predicate);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function __debugInfo(): array
	{
		return $this->rules;
	}

}
