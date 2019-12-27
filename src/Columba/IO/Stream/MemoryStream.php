<?php
/**
 * Copyright (c) 2017 - 2019 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Latte Framework package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\IO\Stream;

use function fopen;

/**
 * Class MemoryStream
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\IO\Stream
 * @since 1.6.0
 */
class MemoryStream extends Stream
{

	/**
	 * MemoryStream constructor.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct()
	{
		parent::__construct(fopen('php://memory', 'wb+'));
	}

}
