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

namespace Columba\Error;

use Columba\Facade\IJson;
use Columba\Util\ExceptionUtil;
use Exception;
use Throwable;

/**
 * Class ColumbaException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Error
 * @since 1.6.0
 */
abstract class ColumbaException extends Exception implements IJson
{

	/**
	 * ColumbaException constructor.
	 *
	 * @param string         $message
	 * @param int            $code
	 * @param Throwable|null $previous
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function jsonSerialize(): array
	{
		return [
			'code' => $this->getCode(),
			'error' => ExceptionUtil::getExceptionCode($this),
			'error_description' => $this->getMessage()
		];
	}

}
