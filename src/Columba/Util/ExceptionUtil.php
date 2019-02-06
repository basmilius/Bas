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

namespace Columba\Util;

use Columba\Error\ExceptionInfo;
use Columba\Http\ResponseCode;
use Generator;
use Throwable;

/**
 * Class ExceptionUtil
 *
 * @package Columba\Util
 * @author Bas Milius <bas@mili.us>
 * @since 1.4.0
 */
final class ExceptionUtil
{

	/**
	 * Converts exceptions to an usable array.
	 *
	 * @param Throwable $err
	 *
	 * @return Generator|ExceptionInfo[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function exceptionsToIterator(Throwable $err): Generator
	{
		yield new ExceptionInfo($err);

		while (($err = $err->getPrevious()) !== null)
			yield new ExceptionInfo($err);
	}

	/**
	 * Gets the exception code from class constants.
	 *
	 * @param Throwable $err
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function getExceptionCode(Throwable $err): string
	{
		$code = '0x' . strtoupper(dechex($err->getCode()));

		if (!ReflectionUtil::findConstant(get_class($err), $err->getCode(), $code))
			ReflectionUtil::findConstant(ResponseCode::class, $err->getCode(), $code);

		return $code;
	}

}
