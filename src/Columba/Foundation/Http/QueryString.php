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

namespace Columba\Foundation\Http;

/**
 * Class QueryString
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Foundation\Http
 * @since 1.5.0
 */
class QueryString extends Parameters
{

	/**
	 * QueryString constructor.
	 *
	 * @param array $queryString
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public function __construct(array $queryString)
	{
		parent::__construct($queryString);
	}

	/**
	 * Creates a querystring from string.
	 *
	 * @param string $queryString
	 *
	 * @return QueryString
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public static function createFromString(string $queryString): self
	{
		parse_str($queryString, $parameters);

		return new self($parameters);
	}

}
