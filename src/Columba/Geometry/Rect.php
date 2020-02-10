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

namespace Columba\Geometry;

/**
 * Class Rect
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Geometry
 * @since 1.6.0
 */
class Rect
{

	protected int $height;
	protected int $width;

	/**
	 * Rect constructor.
	 *
	 * @param int $width
	 * @param int $height
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(int $width, int $height)
	{
		$this->height = $height;
		$this->width = $width;
	}

	/**
	 * Gets the height.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getHeight(): int
	{
		return $this->height;
	}

	/**
	 * Gets the width.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getWidth(): int
	{
		return $this->width;
	}

	/**
	 * Sets the height.
	 *
	 * @param int $height
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function setHeight(int $height): void
	{
		$this->height = $height;
	}

	/**
	 * Sets the width.
	 *
	 * @param int $width
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function setWidth(int $width): void
	{
		$this->width = $width;
	}

}
