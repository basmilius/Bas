<?php
declare(strict_types=1);

namespace Columba\Contract\Rule;

use Columba\Contract\Contract;
use Columba\Contract\Term;
use function class_exists;

/**
 * Class RuleNotExists
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Contract\Rule
 * @since 1.6.0
 */
final class RuleNotExists extends AbstractRule
{

	private string $column;
	private string $model;

	/**
	 * RuleNotExists constructor.
	 *
	 * @param Contract $contract
	 * @param Term $term
	 * @param string $model
	 * @param string $column
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(Contract $contract, Term $term, string $model, string $column)
	{
		parent::__construct($contract, $term);

		$this->column = $column;
		$this->model = $model;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function met(&$value): bool
	{
		if (!class_exists($this->model))
			return $this->breach(sprintf('Model %s does not exist.', $this->model));

		if ($this->model::exists($value))
			return $this->breach('Object found.');

		return true;
	}

}