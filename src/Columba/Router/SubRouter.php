<?php
/**
 * Copyright (c) 2019 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Router;

use Columba\Router\Renderer\AbstractRenderer;
use Columba\Router\Response\AbstractResponse;

/**
 * Class SubRouter
 *
 * @package Columba\Router
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.0
 */
class SubRouter extends Router
{

	/**
	 * @var RouteParam[]
	 */
	private $parameters;

	/**
	 * @var Router
	 */
	private $parent;

	/**
	 * SubRouter constructor.
	 *
	 * @param AbstractResponse|null $response
	 * @param AbstractRenderer|null $renderer
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(?AbstractResponse $response = null, ?AbstractRenderer $renderer = null)
	{
		parent::__construct($response, $renderer);

		$this->parameters = [];
	}

	/**
	 * Adds a sub router parameter.
	 *
	 * @param RouteParam $param
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function addParameter(RouteParam $param): void
	{
		$this->parameters[] = $param;
	}

	/**
	 * Adds multiple sub router parameters.
	 *
	 * @param RouteParam ...$params
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function addParameters(RouteParam ...$params): void
	{
		foreach ($params as $param)
			$this->addParameter($param);
	}

	/**
	 * Gets sub router parameters.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getParameters(): array
	{
		return $this->parameters;
	}

	/**
	 * Gets the parent {@see Router}.
	 *
	 * @return Router
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getParent(): Router
	{
		return $this->parent;
	}

	/**
	 * Sets the parent {@see Router}.
	 *
	 * @param Router $parent
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function setParent(Router $parent): void
	{
		$this->parent = $parent;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function getRenderer(): ?AbstractRenderer
	{
		$renderer = parent::getRenderer();

		if ($renderer !== null)
			return $renderer;

		if ($this->parent !== null)
			return $this->parent->getRenderer();

		return null;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function getResponse(): ?AbstractResponse
	{
		$response = parent::getResponse();

		if ($response !== null)
			return $response;

		if ($this->parent !== null)
			return $this->parent->getResponse();

		return null;
	}

}
