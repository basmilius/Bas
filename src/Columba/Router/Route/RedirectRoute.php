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

namespace Columba\Router\Route;

use Columba\Router\RouteParam;
use Columba\Router\Router;

/**
 * Class RedirectRoute
 *
 * @package Columba\Router\Route
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.1
 */
final class RedirectRoute extends AbstractRoute
{

	/**
	 * @var string
	 */
	private $destination;

	/**
	 * @var RouteParam[]
	 */
	private $params;

	/**
	 * @var int
	 */
	private $responseCode;

	/**
	 * CallbackRoute constructor.
	 *
	 * @param Router $parent
	 * @param string $requestMethod
	 * @param string $path
	 * @param string $destination
	 * @param int    $responseCode
	 * @param array  $options
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.1
	 */
	public function __construct(Router $parent, string $requestMethod, string $path, string $destination, int $responseCode, array $options = [])
	{
		$this->destination = $destination;
		$this->responseCode = $responseCode;

		preg_match_all('/\$([a-zA-Z0-9_]+)\((bool|int|string)\)/', $path, $matches);

		$this->params = [];

		for ($i = 0; $i < count($matches[0]); $i++)
			$this->params[] = new RouteParam($matches[1][$i], $matches[2][$i]);

		$path = preg_replace('/\((bool|int|string)\)/', '', $path);

		parent::__construct($parent, $requestMethod, $path, $options);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.1
	 */
	public final function executeImpl(): void
	{
		$destination = $this->destination;

		foreach ($this->params as $param)
			$destination = str_replace('$' . $param->getName(), $this->getContext()->getParam($param->getName()), $destination);

		$this->getContext()->redirect($destination, $this->responseCode);
	}

	/**
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.1
	 */
	public final function getValidatableParams(): array
	{
		return $this->params;
	}

}
