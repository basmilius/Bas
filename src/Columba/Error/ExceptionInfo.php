<?php
declare(strict_types=1);

namespace Columba\Error;

use Columba\Util\A;
use Columba\Util\ExceptionUtil;
use JsonSerializable;
use Throwable;

/**
 * Class ExceptionInfo
 *
 * @package Columba\Error
 * @author Bas Milius <bas@mili.us>
 * @since 1.4.0
 */
class ExceptionInfo implements JsonSerializable
{

	/**
	 * @var Throwable
	 */
	private $err;

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
		return A::map($this->err->getTrace(), function (array $trace): TraceInfo
		{
			return new TraceInfo($this, $trace);
		});
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
