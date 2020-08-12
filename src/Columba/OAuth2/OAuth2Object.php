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

namespace Columba\OAuth2;

use Columba\Facade\IArray;
use Columba\Facade\IJson;

/**
 * Class OAuth2Object
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\OAuth2
 * @since
 */
class OAuth2Object implements IArray, IJson
{

	protected array $data;
	protected OAuth2 $oAuth2;

	/**
	 * OAuth2Object constructor.
	 *
	 * @param array $data
	 * @param OAuth2 $oAuth2
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(array $data, OAuth2 $oAuth2)
	{
		$this->data = $data;
		$this->oAuth2 = $oAuth2;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function offsetExists($offset): bool
	{
		return isset($this->data[$offset]);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function offsetGet($offset)
	{
		return $this->data[$offset];
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function offsetSet($offset, $value): void
	{
		$this->data[$offset] = $value;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function offsetUnset($offset): void
	{
		unset($this->data[$offset]);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.5.0
	 */
	public final function toArray(): array
	{
		return $this->data;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function jsonSerialize(): array
	{
		return $this->data;
	}

}
