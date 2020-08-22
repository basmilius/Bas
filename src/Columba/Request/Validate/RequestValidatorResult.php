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

namespace Columba\Request\Validate;

use ArrayAccess;
use ErrorException;
use function count;

/**
 * Class RequestValidatorResult
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Request\Validate
 * @since 1.2.0
 */
final class RequestValidatorResult implements ArrayAccess
{

	private array $errors = [];
	private array $params = [];

	/**
	 * Adds an error.
	 *
	 * @param RequestValidatorException $err
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function addError(RequestValidatorException $err): void
	{
		$this->errors[] = $err;
	}

	/**
	 * Adds a valid param.
	 *
	 * @param string $name
	 * @param mixed $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function addParam(string $name, $value): void
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
	public final function getErrors(): array
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
	public final function getParams(): array
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
	public final function isValid(): bool
	{
		return count($this->errors) === 0;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function offsetExists($offset): bool
	{
		return isset($this->params[$offset]);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function offsetGet($offset)
	{
		return $this->params[$offset];
	}

	/**
	 * {@inheritdoc}
	 * @throws ErrorException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function offsetSet($offset, $value)
	{
		throw new ErrorException('Altering validated parameters is not permitted.');
	}

	/**
	 * {@inheritdoc}
	 * @throws ErrorException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function offsetUnset($offset)
	{
		throw new ErrorException('Altering validated parameters is not permitted.');
	}

}
