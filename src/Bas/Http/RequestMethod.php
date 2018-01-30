<?php
/**
 * This file is part of the Bas package.
 *
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bas\Http;

/**
 * Class RequestMethod
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Http
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
