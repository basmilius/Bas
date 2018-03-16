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

namespace Columba\Router\Response;

/**
 * Class HtmlResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router
 * @since 1.0.0
 */
final class HtmlResponse extends AbstractResponse
{

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function print ($data): void
	{
		echo $data;
	}

}
