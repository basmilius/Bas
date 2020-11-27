<?php
/**
 * Copyright (c) 2019 - 2020 - Bas Milius <bas@mili.us>
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
use JetBrains\PhpStorm\ArrayShape;
use function count;
use function preg_match_all;
use function preg_replace;
use function str_replace;

/**
 * Class RedirectRoute
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router\Route
 * @since 1.3.1
 */
final class RedirectRoute extends AbstractRoute
{

	/** @var RouteParam[] */
	private array $params;

	/**
	 * CallbackRoute constructor.
	 *
	 * @param Router $parent
	 * @param string[] $requestMethods
	 * @param string $path
	 * @param string $destination
	 * @param int $responseCode
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.1
	 */
	public function __construct(Router $parent, array $requestMethods, string $path, private string $destination, private int $responseCode)
	{
		preg_match_all('/\$([a-zA-Z0-9_]+)\((bool|int|string)\)/', $path, $matches);

		$this->params = [];

		for ($i = 0, $length = count($matches[0]); $i < $length; ++$i)
			$this->params[] = new RouteParam($matches[1][$i], $matches[2][$i]);

		$path = preg_replace('/\((bool|int|string)\)/', '', $path);

		parent::__construct($parent, $requestMethods, $path);
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

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	#[ArrayShape([
		'path' => 'string',
		'requestMethods' => 'string',
		'middlewares' => '\Columba\Router\Middleware\AbstractMiddleware[]',
		'destination' => 'string'
	])]
	public function __debugInfo(): array
	{
		return array_merge(parent::__debugInfo(), [
			'destination' => $this->destination
		]);
	}

}
