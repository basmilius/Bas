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

namespace Columba\Router;

/**
 * Class RouteParam
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router
 * @since 1.3.0
 */
final class RouteParam
{

	private bool $allowsNull;
	private string $name;
	private string $type;

	/** @var mixed */
	private $defaultValue;

	/**
	 * RouteParam constructor.
	 *
	 * @param string $name
	 * @param string $type
	 * @param bool $allowsNull
	 * @param mixed $defaultValue
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 2.3.0
	 */
	public function __construct(string $name, string $type, bool $allowsNull = false, $defaultValue = null)
	{
		$this->allowsNull = $allowsNull;
		$this->defaultValue = $defaultValue;
		$this->name = $name;
		$this->type = $type;
	}

	/**
	 * Gets regex for this param.
	 *
	 * @return string|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getRegex(): ?string
	{
		switch ($this->type)
		{
			case 'string':
				$regex = '[a-zA-Z0-9-_.@=,]+';
				break;

			case 'int':
				$regex = '[0-9]+';
				break;

			case 'bool':
				$regex = '(1|0|true|false)';
				break;

			default:
				return null;
		}

		$regex = '(?<' . $this->name . '>' . $regex . ')';

		if ($this->defaultValue !== null)
			return '[/.]?' . $regex . '?';

		return '[/.]' . $regex;
	}

	/**
	 * Gets the sanitized value.
	 *
	 * @param string $value
	 *
	 * @return mixed
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function sanitize(string $value)
	{
		switch ($this->type)
		{
			case 'string':
				return $value;

			case 'int':
				return (int)$value;

			case 'bool':
				return $value === '1' || $value === 'true';

			default:
				return null;
		}
	}

	/**
	 * Gets the default value.
	 *
	 * @return mixed
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getDefaultValue()
	{
		return $this->defaultValue;
	}

	/**
	 * Gets the name.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getName(): string
	{
		return $this->name;
	}

	/**
	 * Gets the type.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getType(): string
	{
		return $this->type;
	}

}
