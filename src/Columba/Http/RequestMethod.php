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

namespace Columba\Http;

/**
 * Class RequestMethod
 *
 * @package Columba\Http
 * @author Bas Milius <bas@mili.us>
 * @since 1.2.0
 */
final class RequestMethod
{

	public const DELETE = 'DELETE';
	public const GET = 'GET';
	public const HEAD = 'HEAD';
	public const OPTIONS = 'OPTIONS';
	public const PATCH = 'PATCH';
	public const POST = 'POST';
	public const PUT = 'PUT';

}
