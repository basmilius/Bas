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

namespace Columba\Palette;

use Columba\Color\Color;
use Columba\Color\ColorUtil;
use Columba\Image\Image;
use Exception;

/**
 * Class ColorCutQuantizer
 *
 * @package Columba\Palette
 * @author Bas Milius <bas@mili.us>
 * @since 1.1.0
 */
final class ColorCutQuantizer
{

	public const BLACK_MAX_LIGHTNESS = 0.05;
	public const WHITE_MAX_LIGHTNESS = 0.95;

	public const COMPONENT_RED = -3;
	public const COMPONENT_GREEN = -2;
	public const COMPONENT_BLUE = -1;

	/**
	 * @var int[]
	 */
	private $colors;

	/**
	 * @var int[]
	 */
	private $colorPopulations;

	/**
	 * @var Swatch[]
	 */
	private $quantizedColors;

	/**
	 * ColorCutQuantizer constructor.
	 *
	 * @param ColorHistogram $colorHistogram
	 * @param int            $maxColors
	 *
	 * @throws Exception
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public function __construct(ColorHistogram $colorHistogram, int $maxColors)
	{
		$rawColors = $colorHistogram->getColors();
		$rawColorCounts = $colorHistogram->getColorCounts();
		$validColorCount = 0;

		$this->colorPopulations = [];

		for ($i = 0; $i < count($rawColors); ++$i)
		{
			if (isset($this->colorPopulations[$rawColors[$i]]))
			{
				$this->colorPopulations[$rawColors[$i]] += $rawColorCounts[$i];
			}
			else
			{
				$this->colorPopulations[$rawColors[$i]] = $rawColorCounts[$i];
			}
		}

		$this->colors = [];

		foreach ($rawColors as $color)
		{
			if ($this->shouldIgnoreColor($color))
				continue;

			$this->colors[$validColorCount++] = $color;
		}

		if (true || $validColorCount <= $maxColors)
		{
			$this->quantizedColors = [];

			foreach ($this->colors as $color)
			{
				if (!isset($this->colorPopulations[$color]))
					continue;

				$this->quantizedColors[] = new Swatch(Color::fromRgb(...ColorUtil::intToRgb($color)), $this->colorPopulations[$color]);
			}
		}
		else
		{
			$this->quantizedColors = $this->quantizePixels($validColorCount - 1, $maxColors);
		}
	}

	/**
	 * Gets the colors.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getColors(): array
	{
		return $this->colors;
	}

	/**
	 * Gets the color populaitons.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getColorPopulations(): array
	{
		return $this->colorPopulations;
	}

	/**
	 * Gets the quantized colors.
	 *
	 * @return Swatch[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getQuantizedColors(): array
	{
		return $this->quantizedColors;
	}

	/**
	 * Generates the avarage colors.
	 *
	 * @param Vbox[] $vboxes
	 *
	 * @return Vbox[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private function generateAvarageColors(array $vboxes): array
	{
		$colors = [];

		foreach ($vboxes as $vbox)
			if (!$this->shouldIgnoreSwatch($vbox->getAvarageColor()))
				$colors[] = $vbox->getAvarageColor();

		return $colors;
	}

	/**
	 * Modifies the significant octet.
	 *
	 * @param int $dimension
	 * @param int $lowerIndex
	 * @param int $upperIndex
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function modifySignificantOctet(int $dimension, int $lowerIndex, int $upperIndex): void
	{
		switch ($dimension)
		{
			case self::COMPONENT_RED:
				break;

			case self::COMPONENT_GREEN:
				for ($i = $lowerIndex; $i <= $upperIndex; ++$i)
				{
					$color = $this->colors[$i];
					$this->colors[$i] = ColorUtil::rgbToInt(($color >> 8) & 0xFF, ($color >> 16) & 0xFF, $color & 0xFF);
				}
				break;

			case self::COMPONENT_BLUE:
				for ($i = $lowerIndex; $i <= $upperIndex; ++$i)
				{
					$color = $this->colors[$i];
					$this->colors[$i] = ColorUtil::rgbToInt($color & 0xFF, ($color >> 8) & 0xFF, ($color >> 16) & 0xFF);
				}
				break;
		}
	}

	/**
	 * Quantizes the pixels.
	 *
	 * @param int $maxColorIndex
	 * @param int $maxColors
	 *
	 * @return array
	 * @throws Exception
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private function quantizePixels(int $maxColorIndex, int $maxColors): array
	{
		$pq = [
			new Vbox($this, 0, $maxColorIndex)
		];

		$this->splitBoxes($pq, $maxColors);

		return $this->generateAvarageColors($pq);
	}

	/**
	 * Returns TRUE if {@see $color} should be ignored.
	 *
	 * @param int $color
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private function shouldIgnoreColor(int $color): bool
	{
		$color = Color::fromRgb(...ColorUtil::intToRgb($color));

		return $this->shouldIgnoreHsl($color->getHsl());
	}

	/**
	 * Returns TRUE if {@see $hsl} should be ignored.
	 *
	 * @param array $hsl
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private function shouldIgnoreHsl(array $hsl): bool
	{
		return self::isWhite($hsl) || self::isBlack($hsl) || self::isNearRedILine($hsl);
	}

	/**
	 * Returns TRUE if {@see $swatch} should be ignored.
	 *
	 * @param Swatch $swatch
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private function shouldIgnoreSwatch(Swatch $swatch): bool
	{
		return $this->shouldIgnoreHsl($swatch->getColor()->getHsl());
	}

	/**
	 * Sorts the colors.
	 *
	 * @param int $offset
	 * @param int $length
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function sortColors(int $offset, int $length): void
	{
		$sub = array_slice($this->colors, $offset, $length);
		sort($sub);
		array_splice($this->colors, $offset, $length, $sub);
	}

	/**
	 * Splits the vboxes.
	 *
	 * @param Vbox[] $vboxes
	 * @param int    $maxSize
	 *
	 * @throws Exception
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private function splitBoxes(array $vboxes, int $maxSize)
	{
		while (count($vboxes) < $maxSize)
		{
			$vbox = array_shift($vboxes);

			if ($vbox !== null && $vbox->canSplit())
			{
				$vboxes[] = $vbox->splitBox();
				$vboxes[] = $vbox;
			}
			else
			{
				return;
			}
		}
	}

	/**
	 * Create self from image.
	 *
	 * @param Image $image
	 * @param int   $maxColors
	 *
	 * @return ColorCutQuantizer
	 * @throws Exception
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function fromImage(Image $image, int $maxColors): self
	{
		return new self(new ColorHistogram(iterator_to_array($image->getPixels())), $maxColors);
	}

	/**
	 * Returns TRUE if {@see $hsl} is black.
	 *
	 * @param array $hsl
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private static function isBlack(array $hsl): bool
	{
		return $hsl[2] <= self::BLACK_MAX_LIGHTNESS;
	}

	/**
	 * Returns TRUE if {@see $hsl} is near the red I line.
	 *
	 * @param array $hsl
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private static function isNearRedILine(array $hsl): bool
	{
		return $hsl[0] > 0.0278 && $hsl[0] <= 0.1028 && $hsl[1] <= 0.82;
	}

	/**
	 * Returns TRUE if {@see $hsl} is white.
	 *
	 * @param array $hsl
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private static function isWhite(array $hsl): bool
	{
		return $hsl[2] >= self::WHITE_MAX_LIGHTNESS;
	}

}
