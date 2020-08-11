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

namespace Columba\Contract;

use Columba\Error\ColumbaException;
use Throwable;

/**
 * Class ContractBreachException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Contract
 * @since 1.6.0
 */
final class ContractBreachException extends ColumbaException
{

	public const ERR_VALUE_REQUIRED = 1;
	public const ERR_RULE_NOT_SATISFIABLE = 2;

	private array $errors;

	/**
	 * ContractBreachException constructor.
	 *
	 * @param array $errors
	 * @param string $message
	 * @param int $code
	 * @param Throwable|null $previous
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 2.0.0
	 */
	public function __construct(array $errors, string $message, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);

		$this->errors = $errors;
	}

	/**
	 * Gets the errors.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 2.0.0
	 */
	public final function getErrors(): array
	{
		return $this->errors;
	}

}
