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

namespace Columba\Foundation\Http\RateLimit;

use Columba\Error\ColumbaException;
use Columba\Http\ResponseCode;

/**
 * Class RateLimitExceededException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Foundation\Http\RateLimit
 * @since 1.6.0
 */
final class RateLimitExceededException extends ColumbaException
{

	/**
	 * RateLimitExceededException constructor.
	 *
	 * @param string $message
	 * @param int $code
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(string $message = 'Rate limit exceeded. Try again later.', int $code = ResponseCode::TOO_MANY_REQUESTS)
	{
		parent::__construct($message, $code);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function jsonSerialize(): array
	{
		return [
			'code' => $this->getCode(),
			'error' => 'rate_limit_exceeded',
			'error_description' => $this->getMessage()
		];
	}

}
