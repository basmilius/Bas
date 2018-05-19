<?php
declare(strict_types=1);

namespace Columba\Router\Renderer;

use Columba\Router\RouterException;
use Throwable;

/**
 * Class AbstractRenderer
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router\Renderer
 * @since 1.3.0
 */
abstract class AbstractRenderer
{

	/**
	 * Creates a {@see RouterException}.
	 *
	 * @param Throwable $err
	 *
	 * @return RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected final function error(Throwable $err): RouterException
	{
		return new RouterException('Renderer threw an exception', RouterException::ERR_RENDERER_THREW_EXCEPTION, $err);
	}

	/**
	 * Renders a {@see $template} with a {@see $context}.
	 *
	 * @param string $template
	 * @param array  $context
	 *
	 * @return string
	 * @throws RouterException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public abstract function render(string $template, array $context = []): string;

}
