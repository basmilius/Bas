<?php
/**
 * Copyright (c) 2017 - 2019 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Latte Framework package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Image\GIF;

use Columba\Color\Color;

/**
 * Class GIFColor
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Image\GIF
 * @since 1.6.0
 */
class GIFColor extends Color
{

	protected int $index = -1;

	/**
	 * GIFColor constructor.
	 *
	 * @param int $r
	 * @param int $g
	 * @param int $b
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct(int $r, int $g, int $b)
	{
		parent::__construct($r, $g, $b);
	}

	/**
	 * Gets the color index.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getIndex(): int
	{
		return $this->index;
	}

	/**
	 * Sets the color index.
	 *
	 * @param int $index
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function setIndex(int $index): void
	{
		$this->index = $index;
	}

	/**
	 * Gets the Red color component.
	 *
	 * @param int $r
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function setR(int $r): void
	{
		$this->r = $r;
	}

	/**
	 * Sets the Green color component.
	 *
	 * @param int $g
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function setG(int $g): void
	{
		$this->g = $g;
	}

	/**
	 * Sets the Blue color component.
	 *
	 * @param int $b
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function setB(int $b): void
	{
		$this->b = $b;
	}

}
