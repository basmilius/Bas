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

namespace Columba\OAuth2\Exception;

use Exception;
use JsonSerializable;

/**
 * Class OAuth2Exception
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\OAuth2\Exception
 * @since 1.3.0
 */
abstract class OAuth2Exception extends Exception implements JsonSerializable
{

	/**
	 * OAuth2Exception constructor.
	 *
	 * @param string         $message
	 * @param int            $code
	 * @param Exception|null $previous
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(string $message, int $code = -1, ?Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Gets the error type.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected abstract function getError(): string;

	/**
	 * Gets the response code.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected abstract function getResponseCode(): int;

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function jsonSerialize(): array
	{
		// TODO(Bas): Figure out a way to integrate this with Router.
		http_response_code($this->getResponseCode());

		return [
			'code' => $this->getResponseCode(),
			'error' => $this->getError(),
			'error_description' => $this->message
		];
	}

}
