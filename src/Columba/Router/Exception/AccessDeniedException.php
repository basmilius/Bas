<?php
/**
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Router\Exception;

/**
 * Class AccessDeniedException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router
 * @since 1.0.0
 */
final class AccessDeniedException extends \Exception
{

	public const ERR_ACCESS_DENIED = 0xFA00AC1;

	/**
	 * AccessDeniedException constructor.
	 *
	 * @param string $message
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct(string $message)
	{
		parent::__construct($message, self::ERR_ACCESS_DENIED);
	}

}
