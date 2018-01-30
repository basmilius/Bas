<?php
/**
 * This file is part of the Bas package.
 *
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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