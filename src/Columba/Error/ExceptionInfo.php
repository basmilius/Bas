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
use Throwable;
use function array_map;
use function get_class;

/**
 * Class ExceptionInfo
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Error
 * @since 1.4.0
 */
class ExceptionInfo implements IJson
{

	private Throwable $err;

	/**
	 * ExceptionInfo constructor.
	 *
	 * @param Throwable $err
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function __construct(Throwable $err)
	{
		$this->err = $err;
	}

	/**
	 * Gets the exception code.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function getCode(): int
	{
		return $this->err->getCode();
	}

	/**
	 * Gets the exception code constant.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function getCodeConstant(): string
	{
		return ExceptionUtil::getExceptionCode($this->err);
	}

	/**
	 * Gets the file.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function getFile(): string
	{
		return $this->err->getFile();
	}

	/**
	 * Gets the line in file.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function getLine(): int
	{
		return $this->err->getLine();
	}

	/**
	 * Gets the exception message.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function getMessage(): string
	{
		return $this->err->getMessage();
	}

	/**
	 * Gets the stacktrace.
	 *
	 * @return TraceInfo[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function getTrace(): array
	{
		return array_map(fn(array $trace): TraceInfo => new TraceInfo($this, $trace), $this->err->getTrace());
	}

	/**
	 * Gets the exception class.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function getType(): string
	{
		return get_class($this->err);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function jsonSerialize(): array
	{
		return [
			'code' => $this->getCode(),
			'code_constant' => $this->getCodeConstant(),
			'file' => $this->getFile(),
			'line' => $this->getLine(),
			'message' => $this->getMessage(),
			'trace' => $this->getTrace(),
			'type' => $this->getType()
		];
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function __debugInfo(): array
	{
		return $this->jsonSerialize();
	}

}
