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
use Columba\Color\ColorUtil;
use Exception;

/**
 * Class Vbox
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Palette
 * @since 1.1.0
 */
final class Vbox
{

	/**
	 * @var ColorCutQuantizer
	 */
	private $quantizer;

	/**
	 * @var int
	 */
	private $lowerIndex;

	/**
	 * @var int
	 */
	private $upperIndex;

	/**
	 * @var int
	 */
	private $minRed, $maxRed, $minGreen, $maxGreen, $minBlue, $maxBlue;

	/**
	 * Vbox constructor.
	 *
	 * @param ColorCutQuantizer $quantizer
	 * @param int               $lowerIndex
	 * @param int               $upperIndex
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public function __construct (ColorCutQuantizer $quantizer, int $lowerIndex, int $upperIndex)
	{
		$this->quantizer = $quantizer;
		$this->lowerIndex = $lowerIndex;
		$this->upperIndex = $upperIndex;

		$this->fitBox();
	}

	/**
	 * Returns TRUE if this vbox can split.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function canSplit (): bool
	{
		return $this->getColorCount() > 1;
	}

	/**
	 * Gets the avarage color.
	 *
	 * @return Swatch
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getAvarageColor (): Swatch
	{
		$redSum = 0;
		$greenSum = 0;
		$blueSum = 0;
		$totalPopulation = 0;

		for ($i = $this->lowerIndex; $i <= $this->upperIndex; $i++)
		{
			$color = $this->quantizer->getColors()[$i];
			[$r, $g, $b] = ColorUtil::intToRgb($color);

			if (!isset($this->quantizer->getColorPopulations()[$color]))
				continue;

			$colorPopulation = $this->quantizer->getColorPopulations()[$color];
			$totalPopulation += $colorPopulation;

			$redSum += $colorPopulation * $r;
			$greenSum += $colorPopulation * $g;
			$blueSum += $colorPopulation * $b;
		}

		$redAvarage = (int)round($redSum / $totalPopulation);
		$greenAvarage = (int)round($greenSum / $totalPopulation);
		$blueAvarage = (int)round($blueSum / $totalPopulation);

		return new Swatch(Color::fromRgb($redAvarage, $greenAvarage, $blueAvarage), $totalPopulation);
	}

	/**
	 * Gets the color count.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getColorCount (): int
	{
		return $this->upperIndex - $this->lowerIndex + 1;
	}

	/**
	 * Finds the split point.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private function findSplitPoint (): int
	{
		$longestDimension = $this->getLongestColorDimension();

		$this->quantizer->modifySignificantOctet($longestDimension, $this->lowerIndex, $this->upperIndex);
		$this->quantizer->sortColors($this->lowerIndex, $this->upperIndex - $this->lowerIndex);
		$this->quantizer->modifySignificantOctet($longestDimension, $this->lowerIndex, $this->upperIndex);

		$dimensionMidPoint = $this->midPoint($longestDimension);

		for ($i = $this->lowerIndex; $i <= $this->upperIndex; $i++)
		{
			$color = $this->quantizer->getColors()[$i];

			[$r, $g, $b] = ColorUtil::intToRgb($color);

			switch ($longestDimension)
			{
				case ColorCutQuantizer::COMPONENT_RED:
					if ($r >= $dimensionMidPoint)
						return $i;
					break;
				case ColorCutQuantizer::COMPONENT_GREEN:
					if ($g >= $dimensionMidPoint)
						return $i;
					break;
				case ColorCutQuantizer::COMPONENT_BLUE:
					if ($b > $dimensionMidPoint)
						return $i;
					break;
			}
		}

		return $this->lowerIndex;
	}

	/**
	 * Fits the box.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private function fitBox (): void
	{
		$this->minRed = $this->minGreen = $this->minBlue = 0xFF;
		$this->maxRed = $this->maxGreen = $this->maxBlue = 0x0;

		for ($i = $this->lowerIndex; $i <= $this->upperIndex; $i++)
		{
			$color = $this->quantizer->getColors()[$i];

			[$r, $g, $b] = ColorUtil::intToRgb($color);

			if ($r > $this->maxRed)
				$this->maxRed = $r;

			if ($r < $this->minRed)
				$this->minRed = $r;

			if ($g > $this->maxGreen)
				$this->maxGreen = $g;

			if ($g < $this->minGreen)
				$this->minGreen = $g;

			if ($b > $this->maxBlue)
				$this->maxBlue = $b;

			if ($b < $this->minBlue)
				$this->minBlue = $b;
		}
	}

	/**
	 * Gets the longest color dimension.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private function getLongestColorDimension (): int
	{
		$redLength = $this->maxRed - $this->minRed;
		$greenLength = $this->maxGreen - $this->minGreen;
		$blueLength = $this->maxBlue - $this->minBlue;

		if ($redLength >= $greenLength && $redLength >= $blueLength)
			return ColorCutQuantizer::COMPONENT_RED;

		if ($greenLength >= $redLength && $greenLength >= $blueLength)
			return ColorCutQuantizer::COMPONENT_GREEN;

		return ColorCutQuantizer::COMPONENT_BLUE;
	}

	/**
	 * Gets the mid point.
	 *
	 * @param int $dimension
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private function midPoint (int $dimension): int
	{
		switch ($dimension)
		{
			default:
				return (int)(($this->minRed + $this->maxRed) / 2);

			case ColorCutQuantizer::COMPONENT_GREEN:
				return (int)(($this->minGreen + $this->maxGreen) / 2);

			case ColorCutQuantizer::COMPONENT_BLUE:
				return (int)(($this->minBlue + $this->maxBlue) / 2);
		}
	}

	/**
	 * Split the box.
	 *
	 * @return Vbox
	 * @throws Exception
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function splitBox (): self
	{
		if (!$this->canSplit())
			throw new Exception('Cannot split a box with only one color.');

		$splitPoint = $this->findSplitPoint();
		$newBox = new self($this->quantizer, $splitPoint + 1, $this->upperIndex);

		$this->upperIndex = $splitPoint;
		$this->fitBox();

		return $newBox;
	}

}
