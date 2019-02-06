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
 * Class SerializeResponse
 *
 * @package Columba\Router\Response
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.0
 */
class SerializeResponse extends AbstractResponse
{

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected function respond($value): string
	{
		$this->addHeader('Content-Type', 'text/plain');

		return serialize($value);
	}

}
