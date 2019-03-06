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

namespace Columba\Router\Response;

use Columba\Router\RouteContext;
use Columba\Router\RouterException;

/**
 * Class PlainResponse
 *
 * @package Columba\Router\Response
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.0
 */
class PlainResponse extends AbstractResponse
{

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function respond(RouteContext $context, $value): string
	{
		$this->addHeader('Content-Type: text/plain');

		if (!is_scalar($value))
			throw new RouterException('Response value needs to be scalar.', RouterException::ERR_INVALID_RESPONSE_VALUE);

		return strval($value);
	}

}
