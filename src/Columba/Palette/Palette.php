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

use Columba\Image\Image;

/**
 * Class Palette
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Palette
 * @since 1.1.0
 */
final class Palette
{

	public const CALCULATE_IMAGE_DIMENSION = 100;
	public const DEFAULT_CALCULATE_COLORS = 16;

	public const TARGET_DARK_LUMA = 0.26;
	public const MAX_DARK_LUMA = 0.45;

	public const TARGET_LIGHT_LUMA = 0.74;
	public const MIN_LIGHT_LUMA = 0.55;

	public const TARGET_NORMAL_LUMA = 0.5;
	public const MAX_NORMAL_LUMA = 0.7;
	public const MIN_NORMAL_LUMA = 0.3;

	public const TARGET_MUTED_SATURATION = 0.3;
	public const MAX_MUTED_SATURATION = 0.4;

	public const TARGET_VIBRANT_SATURATION = 1.0;
	public const MIN_VIBRANT_SATURATION = 0.35;

	public const WEIGHT_LUMA = 6.0;
	public const WEIGHT_POPULATION = 1.0;
	public const WEIGHT_SATURATION = 3.0;

	public const MIN_CONTRAST_TITLE_TEXT = 3.0;
	public const MIN_CONTRAST_BODY_TEXT = 4.5;

	/**
	 * @var Swatch|null
	 */
	private $mutedSwatch;

	/**
	 * @var Swatch|null
	 */
	private $vibrantSwatch;

	/**
	 * @var Swatch|null
	 */
	private $darkMutedSwatch;

	/**
	 * @var Swatch|null
	 */
	private $darkVibrantSwatch;

	/**
	 * @var Swatch|null
	 */
	private $lightMutedSwatch;

	/**
	 * @var Swatch|null
	 */
	private $lightVibrantSwatch;

	/**
	 * @var int
	 */
	private $highestPopulation;

	/**
	 * @var Swatch[]
	 */
	private $swatches;

	/**
	 * Palette constructor.
	 *
	 * @param Swatch[] $swatches
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private function __construct (array $swatches)
	{
		$this->swatches = $swatches;
		$this->highestPopulation = $this->findHighestPopulation();

		$this->vibrantSwatch = $this->findColor(self::TARGET_NORMAL_LUMA, self::MIN_NORMAL_LUMA, self::MAX_NORMAL_LUMA, self::TARGET_VIBRANT_SATURATION, self::MIN_VIBRANT_SATURATION, 1.0);
		$this->darkVibrantSwatch = $this->findColor(self::TARGET_DARK_LUMA, 0.0, self::MAX_DARK_LUMA, self::TARGET_VIBRANT_SATURATION, self::MIN_VIBRANT_SATURATION, 1.0);
		$this->lightVibrantSwatch = $this->findColor(self::TARGET_LIGHT_LUMA, self::MIN_LIGHT_LUMA, 1.0, self::TARGET_VIBRANT_SATURATION, self::MIN_VIBRANT_SATURATION, 1.0);

		$this->mutedSwatch = $this->findColor(self::TARGET_NORMAL_LUMA, self::MIN_NORMAL_LUMA, self::MAX_NORMAL_LUMA, self::TARGET_MUTED_SATURATION, 0.0, self::MAX_MUTED_SATURATION);
		$this->darkMutedSwatch = $this->findColor(self::TARGET_DARK_LUMA, 0.0, self::MAX_DARK_LUMA, self::TARGET_MUTED_SATURATION, 0.0, self::MAX_MUTED_SATURATION);
		$this->lightMutedSwatch = $this->findColor(self::TARGET_LIGHT_LUMA, self::MIN_LIGHT_LUMA, 1.0, self::TARGET_MUTED_SATURATION, 0.0, self::MAX_MUTED_SATURATION);
	}

	/**
	 * Gets the default {@see Swatch}.
	 *
	 * @return Swatch|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getDefaultSwatch (): ?Swatch
	{
		return $this->vibrantSwatch ?? $this->mutedSwatch ?? $this->darkVibrantSwatch ?? $this->lightVibrantSwatch ?? $this->darkMutedSwatch ?? $this->lightMutedSwatch ?? $this->swatches[0] ?? null;
	}

	/**
	 * Gets a list with defined swatches.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getDefinedSwatches (): array
	{
		return [
			'Default' => $this->getDefaultSwatch(),

			'Muted' => $this->mutedSwatch,
			'Muted (dark)' => $this->darkMutedSwatch,
			'Muted (light)' => $this->lightMutedSwatch,

			'Vibrant' => $this->vibrantSwatch,
			'Vibrant (dark)' => $this->darkVibrantSwatch,
			'Vibrant (light)' => $this->lightVibrantSwatch
		];
	}

	public final function getMutedSwatch (): ?Swatch
	{
		return $this->mutedSwatch;
	}

	public final function getVibrantSwatch (): ?Swatch
	{
		return $this->vibrantSwatch;
	}

	/**
	 * Returns TRUE if {@see $swatch} is already used.
	 *
	 * @param Swatch $swatch
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private function isSwatchUsed (Swatch $swatch): bool
	{
		return
			($this->vibrantSwatch !== null && $this->vibrantSwatch->equals($swatch)) ||
			($this->mutedSwatch !== null && $this->mutedSwatch->equals($swatch)) ||
			($this->darkVibrantSwatch !== null && $this->darkVibrantSwatch->equals($swatch)) ||
			($this->darkMutedSwatch !== null && $this->darkMutedSwatch->equals($swatch)) ||
			($this->lightVibrantSwatch !== null && $this->lightVibrantSwatch->equals($swatch)) ||
			($this->lightMutedSwatch !== null && $this->lightMutedSwatch->equals($swatch));
	}

	/**
	 * Finds a {@see Swatch} color.
	 *
	 * @param float $targetLuma
	 * @param float $minLuma
	 * @param float $maxLuma
	 * @param float $targetSaturation
	 * @param float $minSaturation
	 * @param float $maxSaturation
	 *
	 * @return Swatch|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private function findColor (float $targetLuma, float $minLuma, float $maxLuma, float $targetSaturation, float $minSaturation, float $maxSaturation): ?Swatch
	{
		$max = null;
		$maxValue = 0;

		foreach ($this->swatches as $swatch)
		{
			$hsl = $swatch->getColor()->getHsl();
			$sat = $hsl[1];
			$luma = $hsl[2];

			if ($sat >= $minSaturation && $sat <= $maxSaturation && $luma >= $minLuma && $luma <= $maxLuma && !$this->isSwatchUsed($swatch))
			{
				$thisValue = self::createComparsionValue($sat, $targetSaturation, $luma, $targetLuma, $swatch->getPopulation(), $this->highestPopulation);

				if ($max === null || $thisValue > $maxValue)
				{
					$max = $swatch;
					$maxValue = $thisValue;
				}
			}
		}

		return $max;
	}

	/**
	 * Returns the highest population.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private function findHighestPopulation (): int
	{
		$h = 0;

		foreach ($this->swatches as $swatch)
			if ($swatch->getPopulation() > $h)
				$h = $swatch->getPopulation();

		return $h;
	}

	/**
	 * Creates a comparsion value.
	 *
	 * @param float $saturation
	 * @param float $targetSaturation
	 * @param float $luma
	 * @param float $targetLuma
	 * @param int   $population
	 * @param int   $highestPopulaiton
	 *
	 * @return float
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private static function createComparsionValue (float $saturation, float $targetSaturation, float $luma, float $targetLuma, int $population, int $highestPopulaiton): float
	{
		return self::weightedMean(self::invertDiff($saturation, $targetSaturation), self::WEIGHT_SATURATION, self::invertDiff($luma, $targetLuma), self::WEIGHT_LUMA, $population / $highestPopulaiton, self::WEIGHT_POPULATION);
	}

	/**
	 * Generates a {@see Palette} from {@see Image}.
	 *
	 * @param Image $image
	 * @param int   $numColors
	 *
	 * @return Palette
	 * @throws \Exception
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function generate (Image $image, int $numColors = self::DEFAULT_CALCULATE_COLORS): self
	{
		$image = self::scaleImageDown($image);
		$quantizer = ColorCutQuantizer::fromImage($image, $numColors);

		if ($quantizer !== null)
			return new self($quantizer->getQuantizedColors());

		return null;
	}

	/**
	 * Some Googly calculation.
	 *
	 * @param float $value
	 * @param float $targetValue
	 *
	 * @return float
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private static function invertDiff (float $value, float $targetValue): float
	{
		return 1 - abs($value - $targetValue);
	}

	/**
	 * Scales the image down.
	 *
	 * @param Image $image
	 *
	 * @return Image
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private static function scaleImageDown (Image $image): Image
	{
		$minDimension = min($image->getWidth(), $image->getHeight());

		if ($minDimension <= self::CALCULATE_IMAGE_DIMENSION)
			return $image;

		$scaleRatio = self::CALCULATE_IMAGE_DIMENSION / $minDimension;

		return $image->resize(intval($image->getWidth() * $scaleRatio), intval($image->getHeight() * $scaleRatio));
	}

	/**
	 * Some Googly calculation.
	 *
	 * @param float ...$values
	 *
	 * @return float
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private static function weightedMean (float ...$values): float
	{
		$sum = 0;
		$sumWeight = 0;

		for ($i = 0; $i < count($values); $i += 2)
		{
			$value = $values[$i];
			$weight = $values[$i + 1];

			$sum += $value * $weight;
			$sumWeight += $weight;
		}

		return $sum / $sumWeight;
	}

}
