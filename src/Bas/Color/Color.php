<?php
declare(strict_types=1);

namespace Bas\Color;

/**
 * Class Color
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Color
 * @since 1.1.0
 */
class Color
{

	/**
	 * @var int
	 */
	protected $r;

	/**
	 * @var int
	 */
	protected $g;

	/**
	 * @var int
	 */
	protected $b;

	/**
	 * @var float
	 */
	protected $a;

	/**
	 * Color constructor.
	 *
	 * @param int   $r
	 * @param int   $g
	 * @param int   $b
	 * @param float $a
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public function __construct (int $r, int $g, int $b, float $a = 1.0)
	{
		$this->r = $r;
		$this->g = $g;
		$this->b = $b;
		$this->a = $a;
	}

	/**
	 * Gets the HSL value of this {@see Color}.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getHsl (): array
	{
		return ColorUtil::rgbToHsl($this->r, $this->g, $this->b);
	}

	/**
	 * Gets the RGB value of this {@see Color}.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getRgb (): array
	{
		return [$this->r, $this->g, $this->b];
	}

	/**
	 * Gets the RGBA value of this {@see $color}.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getRgba (): array
	{
		return [$this->r, $this->g, $this->b, $this->a];
	}

}
