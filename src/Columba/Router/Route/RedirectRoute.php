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
use function count;
use function preg_match_all;
use function preg_replace;
use function str_replace;

/**
 * Class RedirectRoute
 *
 * @package Columba\Router\Route
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.1
 */
final class RedirectRoute extends AbstractRoute
{

	private string $destination;
	private int $responseCode;

	/** @var RouteParam[] */
	private array $params;

	/**
	 * CallbackRoute constructor.
	 *
	 * @param Router   $parent
	 * @param string[] $requestMethods
	 * @param string   $path
	 * @param string   $destination
	 * @param int      $responseCode
	 * @param array    $options
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.1
	 */
	public function __construct(Router $parent, array $requestMethods, string $path, string $destination, int $responseCode, array $options = [])
	{
		$this->destination = $destination;
		$this->responseCode = $responseCode;

		preg_match_all('/\$([a-zA-Z0-9_]+)\((bool|int|string)\)/', $path, $matches);

		$this->params = [];

		for ($i = 0, $length = count($matches[0]); $i < $length; ++$i)
			$this->params[] = new RouteParam($matches[1][$i], $matches[2][$i]);

		$path = preg_replace('/\((bool|int|string)\)/', '', $path);

		parent::__construct($parent, $requestMethods, $path, $options);
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
