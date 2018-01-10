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

	/**
	 * Creates a new instance of {@see Color} from HSL values.
	 *
	 * @param float $h
	 * @param float $s
	 * @param float $l
	 *
	 * @return Color
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.1.0
	 */
	public static function fromHsl (float $h, float $s, float $l): self
	{
		[$r, $g, $b] = ColorUtil::hslToRgb($h, $s, $l);

		return new self($r, $g, $b);
	}

	/**
	 * Creates a new instance of {@see Color} from RGB values.
	 *
	 * @param int $r
	 * @param int $g
	 * @param int $b
	 *
	 * @return Color
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.1.0
	 */
	public static function fromRgb (int $r, int $g, int $b): self
	{
		return new self($r, $g, $b);
	}

	/**
	 * Creates a new instance of {@see Color} from RGBA values.
	 *
	 * @param int   $r
	 * @param int   $g
	 * @param int   $b
	 * @param float $a
	 *
	 * @return Color
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.1.0
	 */
	public static function fromRgba (int $r, int $g, int $b, float $a): self
	{
		return new self($r, $g, $b, $a);
	}

}
