<?php
declare(strict_types=1);

namespace Bas\Router;

/**
 * Class AccessDeniedException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Router
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
	public function __construct (string $message)
	{
		parent::__construct($message, self::ERR_ACCESS_DENIED);
	}

}
