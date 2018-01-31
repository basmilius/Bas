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

namespace Columba\Palette;

use Columba\Color\Color;

/**
 * Class Swatch
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Palette
 * @since 1.1.0
 */
final class Swatch
{

	/**
	 * @var Color
	 */
	private $color;

	/**
	 * @var int
	 */
	private $population;

	/**
	 * @var bool
	 */
	private $generatedTextColors = false;

	/**
	 * @var Color
	 */
	private $textColorBody;

	/**
	 * @var Color
	 */
	private $textColorTitle;

	/**
	 * Swatch constructor.
	 *
	 * @param Color $color
	 * @param int   $population
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct (Color $color, int $population)
	{
		$this->color = $color;
		$this->population = $population;
	}

	/**
	 * Gets the {@see Color}.
	 *
	 * @return Color
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getColor (): Color
	{
		return $this->color;
	}

	/**
	 * Gets the population.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getPopulation (): int
	{
		return $this->population;
	}

	/**
	 * Gets the body text {@see Color}.
	 *
	 * @return Color
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getBodyTextColor (): Color
	{
		$this->ensureTextColorsAreGenerated();

		return $this->textColorBody;
	}

	/**
	 * Gets the title text {@see Color}.
	 *
	 * @return Color
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getTitleTextColor (): Color
	{
		$this->ensureTextColorsAreGenerated();

		return $this->textColorTitle;
	}

	/**
	 * Returns TRUE if {@see $other} is the same {@see Swatch}.
	 *
	 * @param Swatch $other
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function equals (self $other): bool
	{
		return $this->color->equals($other->color) && $this->population === $other->population;
	}

	/**
	 * Ensures that text colors are generated.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private function ensureTextColorsAreGenerated (): void
	{
		if ($this->generatedTextColors)
			return;

		// TODO(Bas): Implement this!

		$this->textColorBody = null;
		$this->textColorTitle = null;

		$this->generatedTextColors = true;
	}

}
