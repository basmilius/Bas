<?php
declare(strict_types=1);

namespace Bas\Router;

/**
 * Class HtmlResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Router
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
