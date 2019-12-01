<?php
/**
 * Copyright (c) 2019 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Request\Validate;

/**
 * Class RequestValidator
 *
 * @package Columba\Request\Validate
 * @author Bas Milius <bas@mili.us>
 * @since 1.2.0
 */
final class RequestValidator
{

	private array $options = [];
	private array $params = [];

	/**
	 * Validates {@see $params} with our options.
	 *
	 * @param array $params
	 *
	 * @return RequestValidatorResult
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function toValidate(array $params): RequestValidatorResult
	{
		$this->params = $params;

		$result = new RequestValidatorResult();

		foreach ($this->options as $option)
		{
			$name = $option->getName();

			try
			{
				$value = $this->params[$name] ?? null;

				$option->validate($value);
				$result->addParam($name, $value);
			}
			catch (RequestValidatorException $err)
			{
				$result->addError($err);
				$result->addParam($name, null);
			}
		}

		return $result;
	}

	/**
	 * Sets the request param options.
	 *
	 * @param RequestValidatorOption ...$options
	 *
	 * @return RequestValidator
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function with(RequestValidatorOption ...$options): RequestValidator
	{
		$this->options = $options;

		return $this;
	}

	/**
	 * Use the {@see RequestValidator}.
	 *
	 * @return RequestValidator
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public static function use(): RequestValidator
	{
		return new RequestValidator();
	}

}
