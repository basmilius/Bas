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

namespace Columba\Router\Response;

/**
 * Class ResponseWrapper
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router\Response
 * @since 1.3.0
 */
final class ResponseWrapper
{

	/**
	 * ResponseWrapper constructor.
	 *
	 * @param AbstractResponse $response
	 * @param mixed $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(private AbstractResponse $response, private mixed $value)
	{
	}

	/**
	 * Gets the response.
	 *
	 * @return AbstractResponse
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getResponse(): AbstractResponse
	{
		return $this->response;
	}

	/**
	 * Gets the value.
	 *
	 * @return mixed
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getValue(): mixed
	{
		return $this->value;
	}

}
