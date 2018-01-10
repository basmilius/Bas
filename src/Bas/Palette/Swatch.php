<?php
declare(strict_types=1);

namespace Bas\Palette;

use Bas\Color\Color;

/**
 * Class Swatch
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Palette
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

	public final function getColor (): Color
	{
		return $this->color;
	}

	public final function getPopulation (): int
	{
		return $this->population;
	}

}
