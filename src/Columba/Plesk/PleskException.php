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

namespace Columba\Plesk;

use Columba\Facade\IJson;
use Exception;
use Throwable;

/**
 * Class PleskException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Plesk
 * @since 1.4.0
 */
final class PleskException extends Exception implements IJson
{

	/**
	 * PleskException constructor.
	 *
	 * @param string $message
	 * @param int $code
	 * @param Throwable|null $previous
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public final function jsonSerialize(): array
	{
		return [
			'code' => $this->getCode(),
			'message' => $this->getMessage(),
			'previous' => $this->getPrevious()
		];
	}

}
