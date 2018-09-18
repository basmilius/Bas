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

namespace Columba\Plesk;

use Exception;
use JsonSerializable;
use Throwable;

/**
 * Class PleskException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Plesk
 * @since 1.4.0
 */
final class PleskException extends Exception implements JsonSerializable
{

	/**
	 * PleskException constructor.
	 *
	 * @param string         $message
	 * @param int            $code
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
	public final function jsonSerialize()
	{
		return [
			'code' => $this->getCode(),
			'message' => $this->getMessage(),
			'previous' => $this->getPrevious()
		];
	}

}
