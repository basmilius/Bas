<?php
declare(strict_types=1);

namespace Columba\Error;

use JsonSerializable;

/**
 * Class TraceInfo
 *
 * @package Columba\Error
 * @author Bas Milius <bas@mili.us>
 * @since 1.4.0
 */
class TraceInfo implements JsonSerializable
{

	/**
	 * @var ExceptionInfo
	 */
	private $exceptionInfo;

	/**
	 * @var array
	 */
	private $trace;

	/**
	 * TraceInfo constructor.
	 *
	 * @param ExceptionInfo $exceptionInfo
	 * @param array         $trace
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function __construct(ExceptionInfo $exceptionInfo, array $trace)
	{
		$this->exceptionInfo = $exceptionInfo;
		$this->trace = $trace;
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
		return $this->trace['file'] ?? $this->exceptionInfo->getFile();
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
		return $this->trace['line'] ?? $this->exceptionInfo->getLine();
	}

	/**
	 * Gets the method.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function getMethod(): string
	{
		if (isset($this->trace['class']))
			return sprintf('%s::%s', $this->trace['class'], $this->trace['function']);

		return $this->trace['function'];
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function jsonSerialize(): array
	{
		return [
			'file' => $this->getFile(),
			'line' => $this->getLine(),
			'method' => $this->getMethod()
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
