<?php
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

	/**
	 * @var bool
	 */
	private $allowsNull;

	/**
	 * @var mixed
	 */
	private $defaultValue;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * RouteParam constructor.
	 *
	 * @param string $name
	 * @param string $type
	 * @param bool   $allowsNull
	 * @param mixed  $defaultValue
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
			case 'bool':
				$regex = '(0|false|1|true)';
				break;

			case 'int':
				$regex = '([0-9]+)';
				break;

			case 'string':
				$regex = '([a-zA-Z0-9-_.@=,]+)';
				break;

			default:
				return null;
		}

		return '(?:/' . $regex . ')' . ($this->defaultValue !== null ? '?' : '');
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
			case 'bool':
				return $value === '1' || $value === 'true';

			case 'int':
				return intval($value);

			case 'string':
				return $value;

			default:
				return null;
		}
	}

	/**
	 * Returns TRUE if this param allows NULL.
	 *
	 * @return bool
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function allowsNull(): bool
	{
		return $this->allowsNull;
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
