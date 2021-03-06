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

use Columba\Facade\Debuggable;
use Columba\Facade\Jsonable;
use function sprintf;

/**
 * Class TraceInfo
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Error
 * @since 1.4.0
 */
class TraceInfo implements Debuggable, Jsonable
{

	private ExceptionInfo $exceptionInfo;
	private array $trace;

	/**
	 * TraceInfo constructor.
	 *
	 * @param ExceptionInfo $exceptionInfo
	 * @param array $trace
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
