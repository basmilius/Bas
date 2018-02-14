<?php
/**
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Request\Validate;

/**
 * Class RequestValidatorResult
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Request\Validate
 * @since 1.2.0
 */
final class RequestValidatorResult
{

	/**
	 * @var RequestValidatorException[]
	 */
	private $errors;

	/**
	 * @var mixed[]
	 */
	private $params;

	/**
	 * RequestValidatorResult constructor.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public function __construct ()
	{
		$this->errors = [];
		$this->params = [];
	}

	/**
	 * Adds an error.
	 *
	 * @param RequestValidatorException $err
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function addError (RequestValidatorException $err): void
	{
		$this->errors[] = $err;
	}

	/**
	 * Adds a valid param.
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function addParam (string $name, $value): void
	{
		$this->params[$name] = $value;
	}

	/**
	 * Gets our errors.
	 *
	 * @return RequestValidatorException[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getErrors (): array
	{
		return $this->errors;
	}

	/**
	 * Gets our params.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getParams (): array
	{
		return $this->params;
	}

	/**
	 * Returns TRUE if the request was valid.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function isValid (): bool
	{
		return count($this->errors) === 0;
	}

}
