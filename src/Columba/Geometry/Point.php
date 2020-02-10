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
 * Class Point
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Geometry
 * @since 1.6.0
 */
class Point
{

	protected int $x;
	protected int $y;

	/**
	 * Point constructor.
	 *
	 * @param int $x
	 * @param int $y
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(int $x, int $y)
	{
		$this->x = $x;
		$this->y = $y;
	}

	/**
	 * Gets the X-coord.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getX(): int
	{
		return $this->x;
	}

	/**
	 * Gets the Y-coord.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getY(): int
	{
		return $this->y;
	}

	/**
	 * Sets the X-coord.
	 *
	 * @param int $x
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function setX(int $x): void
	{
		$this->x = $x;
	}

	/**
	 * Sets the Y-coord.
	 *
	 * @param int $y
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function setY(int $y): void
	{
		$this->y = $y;
	}

}
