<?php
declare(strict_types=1);

namespace Bas\Color;

use JsonSerializable;

/**
 * Class Color
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Color
 * @since 1.1.0
 */
class Color implements JsonSerializable
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
	 * Gets the luminance of this {@see Color}.
	 *
	 * @return float
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getLuminance (): float
	{
		return ColorUtil::luminance($this->r, $this->g, $this->b);
	}

	/**
	 * Returns TRUE if this is a dark {@see Color}.
	 *
	 * @param float $delta
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function isDark (float $delta = 0.5): bool
	{
		return $this->getLuminance() < $delta;
	}

	/**
	 * Returns TRUE if this is a light {@see Color}.
	 *
	 * @param float $delta
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function isLight (float $delta = 0.5): bool
	{
		return $this->getLuminance() >= $delta;
	}

	/**
	 * Blends with another {@see Color}.
	 *
	 * @param Color $other
	 * @param int   $weight
	 *
	 * @return Color
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function blend (self $other, int $weight): self
	{
		if ($weight === 0)
			return $this;

		[$r, $g, $b] = ColorUtil::blend([$this->r, $this->g, $this->b], [$other->r, $other->g, $other->b], $weight);

		return self::fromRgba($r, $g, $b, $this->a);
	}

	/**
	 * Gets a shade by {@see $weight} from this {@see Color}.
	 *
	 * @param int $weight
	 *
	 * @return Color
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function shade (int $weight): self
	{
		[$r, $g, $b] = ColorUtil::shade([$this->r, $this->g, $this->b], $weight);

		return self::fromRgba($r, $g, $b, $this->a);
	}

	/**
	 * Gets a tint by {@see $weight} from this {@see Color}.
	 *
	 * @param int $weight
	 *
	 * @return Color
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function tint (int $weight): self
	{
		[$r, $g, $b] = ColorUtil::tint([$this->r, $this->g, $this->b], $weight);

		return self::fromRgba($r, $g, $b, $this->a);
	}

	/**
	 * Gets the R color channel value.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getR (): int
	{
		return $this->r;
	}

	/**
	 * Gets the G color channel value.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getG (): int
	{
		return $this->g;
	}

	/**
	 * Gets the B color channel value.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getB (): int
	{
		return $this->b;
	}

	/**
	 * Gets the A color channel value.
	 *
	 * @return float
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getA (): float
	{
		return $this->a;
	}

	/**
	 * Gets the HEX value of this {@see Color}.
	 *
	 * @param bool $includeHashtag
	 * @param bool $withAlpha
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getHex (bool $includeHashtag = false, bool $withAlpha = false): string
	{
		if ($withAlpha)
			return ColorUtil::rgbaToHex($this->r, $this->g, $this->b, $this->a, $includeHashtag);

		return ColorUtil::rgbToHex($this->r, $this->g, $this->b, $includeHashtag);
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
	 * @author Bas Milius <bas@mili.us>
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
	 * @author Bas Milius <bas@mili.us>
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
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function fromRgba (int $r, int $g, int $b, float $a): self
	{
		return new self($r, $g, $b, $a);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function jsonSerialize (): array
	{
		return [
			'alpha' => $this->r,
			'red' => $this->r,
			'green' => $this->g,
			'blue' => $this->b,
			'hex' => $this->getHex(true),
			'hexa' => $this->getHex(true, true),
			'hsl' => $this->getHsl(),
			'rgb' => [$this->r, $this->g, $this->b],
			'rgba' => [$this->r, $this->g, $this->b, $this->a]
		];
	}

}
