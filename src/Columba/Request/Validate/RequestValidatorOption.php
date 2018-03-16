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

namespace Columba\Request\Validate;

/**
 * Class RequestValidatorOption
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Request\Validate
 * @since 1.2.0
 */
final class RequestValidatorOption
{

	/**
	 * @var string
	 */
	private $fieldName;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var callable[]
	 */
	private $validators;

	/**
	 * RequestValidatorOption constructor.
	 *
	 * @param string $name
	 * @param string $fieldName
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	private function __construct (string $name, string $fieldName)
	{
		$this->fieldName = $fieldName;
		$this->name = $name;
		$this->validators = [];
	}

	/**
	 * Gets the request param name.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getName (): string
	{
		return $this->name;
	}

	/**
	 * Validates our param.
	 *
	 * @param mixed $value
	 *
	 * @throws RequestValidatorException
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function validate (&$value): void
	{
		foreach ($this->validators as $validator)
		{
			[$newValue, $error] = $validator($value);

			if ($error !== null)
				throw new RequestValidatorException($this->fieldName, $error);

			$value = $newValue;
		}
	}

	/**
	 * Ensures that {@see $value} matches {@see $pattern}.
	 * @param string $pattern
	 *
	 * @return RequestValidatorOption
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function matchesWith (string $pattern): RequestValidatorOption
	{
		$this->validators[] = function ($value) use($pattern): array
		{
			if (preg_match($pattern, $value))
				return [$value, null];

			return [null, RequestValidatorException::ERR_DIDNT_MATCH];
		};

		return $this;
	}

	/**
	 * Ensures that {@see $value} succeeds {@see $validator}.
	 *
	 * @param callable $validator
	 *
	 * @return RequestValidatorOption
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function toBe (callable $validator): RequestValidatorOption
	{
		$this->validators[] = $validator;

		return $this;
	}

	/**
	 * Ensures that {@see $value} is greater than {@see $num}.
	 *
	 * @param int|float|double $num
	 *
	 * @return RequestValidatorOption
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function toBeGreaterThan ($num): RequestValidatorOption
	{
		$this->validators[] = function ($value) use ($num): array
		{
			if ($value > $num)
				return [$value, null];

			return [null, RequestValidatorException::ERR_TOO_LOW];
		};

		return $this;
	}

	/**
	 * Ensures that {@see $value} is greater or equal to {@see $num}.
	 *
	 * @param int|float|double $num
	 *
	 * @return RequestValidatorOption
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function toBeGreaterOrEqualTo ($num): RequestValidatorOption
	{
		$this->validators[] = function ($value) use ($num): array
		{
			if ($value >= $num)
				return [$value, null];

			return [null, RequestValidatorException::ERR_TOO_LOW];
		};

		return $this;
	}

	/**
	 * Ensures that {@see $value} is lower than {@see $num}.
	 *
	 * @param int|float|double $num
	 *
	 * @return RequestValidatorOption
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function toBeLowerThan ($num): RequestValidatorOption
	{
		$this->validators[] = function ($value) use ($num): array
		{
			if ($value < $num)
				return [$value, null];

			return [null, RequestValidatorException::ERR_TOO_HIGH];
		};

		return $this;
	}

	/**
	 * Ensures that {@see $value} is lower or equal to {@see $num}.
	 *
	 * @param int|float|double $num
	 *
	 * @return RequestValidatorOption
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function toBeLowerOrEqualTo ($num): RequestValidatorOption
	{
		$this->validators[] = function ($value) use ($num): array
		{
			if ($value <= $num)
				return [$value, null];

			return [null, RequestValidatorException::ERR_TOO_HIGH];
		};

		return $this;
	}

	/**
	 * Ensures that {@see $value} is a boolean.
	 *
	 * @return RequestValidatorOption
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function toBeBoolean (): RequestValidatorOption
	{
		$this->validators[] = function ($value): array
		{
			if ($value || $value === '1' || $value === 1 || $value === 'true')
				return [true, null];

			if (!$value || $value === '0' || $value === 0 || $value === 'false')
				return [false, null];

			return [null, RequestValidatorException::ERR_NEEDS_TO_BE_BOOLEAN];
		};

		return $this;
	}

	/**
	 * Ensures that {@see $value} is a float.
	 *
	 * @return RequestValidatorOption
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function toBeFloat (): RequestValidatorOption
	{
		$this->validators[] = function ($value): array
		{
			if (is_numeric($value))
				return [floatval($value), null];

			return [null, RequestValidatorException::ERR_NEEDS_TO_BE_FLOAT];
		};

		return $this;
	}

	/**
	 * Ensures that {@see $value} is an integer.
	 *
	 * @return RequestValidatorOption
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function toBeInteger (): RequestValidatorOption
	{
		$this->validators[] = function ($value): array
		{
			if (is_numeric($value) && intval($value) == floatval($value))
				return [intval($value), null];

			return [null, RequestValidatorException::ERR_NEEDS_TO_BE_INTEGER];
		};

		return $this;
	}

	/**
	 * Ensures that {@see $value} is a string.
	 *
	 * @return RequestValidatorOption
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function toBeString (): RequestValidatorOption
	{
		$this->validators[] = function ($value): array
		{
			if (!is_string($value))
				return [null, RequestValidatorException::ERR_NEEDS_TO_BE_STRING];

			return [strval($value), null];
		};

		return $this;
	}

	/**
	 * Ensures that {@see $value} is an e-mailaddress.
	 *
	 * @return RequestValidatorOption
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function toBeEmail (): RequestValidatorOption
	{
		$this->validators[] = function ($value): array
		{
			if (filter_var($value, FILTER_VALIDATE_EMAIL))
				return [$value, null];

			return [null, RequestValidatorException::ERR_NOT_AN_EMAIL];
		};

		return $this;
	}

	/**
	 * Ensures that {@see $value} is an url. If {@see $onlySecure} is TRUE, it also checks if it's a secure url.
	 *
	 * @param bool $onlySecure
	 *
	 * @return RequestValidatorOption
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function toBeUrl (bool $onlySecure = false): RequestValidatorOption
	{
		$this->validators[] = function ($value) use ($onlySecure): array
		{
			if (filter_var($value, FILTER_VALIDATE_URL))
			{
				if ($onlySecure && substr($value, 0, 5) !== 'https')
					return [null, RequestValidatorException::ERR_NOT_A_SECURE_URL];

				return [$value, null];
			}

			return [null, RequestValidatorException::ERR_NOT_AN_URL];
		};

		return $this;
	}

	/**
	 * Ensures that {@see $value} exists.
	 *
	 * @return RequestValidatorOption
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function toExist (): RequestValidatorOption
	{
		$this->validators[] = function ($value): array
		{
			if ($value === null)
				return [null, RequestValidatorException::ERR_MISSING];

			return [$value, null];
		};

		return $this;
	}

	/**
	 * Ensures {@see $value} is at least {@see $min} and {@see $max} characters long.
	 *
	 * @param int|null $min
	 * @param int|null $max
	 *
	 * @return RequestValidatorOption
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function withCharsBetween (?int $min = null, ?int $max = null): RequestValidatorOption
	{
		$this->validators[] = function (string $value) use ($min, $max): array
		{
			if ($min !== null && mb_strlen($value) < $min)
				return [null, RequestValidatorException::ERR_TOO_SHORT];

			if ($max !== null && mb_strlen($value) > $max)
				return [null, RequestValidatorException::ERR_TOO_LONG];

			return [$value, null];
		};

		return $this;
	}

	/**
	 * Creates a {@see RequestValidatorOption}.
	 *
	 * @param string      $name
	 * @param string|null $fieldName
	 *
	 * @return RequestValidatorOption
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public static function expect (string $name, ?string $fieldName = null): RequestValidatorOption
	{
		return new RequestValidatorOption($name, $fieldName ?? $name);
	}

}
