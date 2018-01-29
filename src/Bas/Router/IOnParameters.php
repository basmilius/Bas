<?php
declare(strict_types=1);

namespace Bas\Router;

/**
 * Interface IOnParameters
 *
 * @author Bas Milius <bas@ideemedia.nl>
 * @package Bas\Router
 * @since 1.1.0
 */
interface IOnParameters
{

	/**
	 * Invoked when parameters are complete before handle.
	 *
	 * @param array $parameters
	 * @param array $rawParameters
	 *
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.1.0
	 */
	function onParameters (array $parameters, array $rawParameters): void;

}
