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

	/**
	 * AccessDeniedException constructor.
	 *
	 * @param string $capability
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct (string $capability)
	{
		parent::__construct("De functie '$capability' is niet geactiveerd in jouw account.", 0xFA00AC1);
	}

}
