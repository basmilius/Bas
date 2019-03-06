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

/**
 * Class JavaScriptResponse
 *
 * @package Columba\Router\Response
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.0
 */
class JavaScriptResponse extends AbstractResponse
{

	/**
	 * JavaScriptResponse constructor.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct()
	{
		parent::__construct();

		$this->addHeader('Content-Type', 'text/javascript; charset=utf-8');
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected function respond(RouteContext $context, $value): string
	{
		return $value;
	}

}
