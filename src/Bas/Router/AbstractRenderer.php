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
 * Class AbstractRenderer
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Router
 * @since 3.0.0
 */
abstract class AbstractRenderer
{

	/**
	 * Renders a {@see $template} with the given {@see $context}.
	 *
	 * @param string $template
	 * @param array  $context
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public abstract function render (string $template, array $context = []): string;

}
