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

}
