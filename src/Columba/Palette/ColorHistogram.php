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

/**
 * Class ColorHistogram
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Palette
 * @since 1.1.0
 */
final class ColorHistogram
{

	private $colors;
	private $colorCounts;
	private $numberOfColors;

	/**
	 * ColorHistogram constructor.
	 *
	 * @param int[] $pixels
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public function __construct (array $pixels)
	{
		sort($pixels);

		$this->numberOfColors = $this->countDistinctColors($pixels);

		$this->colors = array_fill(0, $this->numberOfColors, 0);
		$this->colorCounts = array_fill(0, $this->numberOfColors, 0);

		$this->countFrequencies($pixels);
	}

	/**
	 * Gets the colors.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getColors (): array
	{
		return $this->colors;
	}

	/**
	 * Gets the color counts.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getColorCounts (): array
	{
		return $this->colorCounts;
	}

	/**
	 * Gets the number of colors.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getNumberOfColors (): int
	{
		return $this->numberOfColors;
	}

	/**
	 * Counts the distinct colors.
	 *
	 * @param int[] $pixels
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private function countDistinctColors (array $pixels): int
	{
		if (count($pixels) < 2)
			return count($pixels);

		$colorCount = 1;
		$currentColor = $pixels[0];

		for ($i = 1; $i < count($pixels); $i++)
		{
			if ($pixels[$i] === $currentColor)
				continue;

			$currentColor = $pixels[$i];
			$colorCount++;
		}

		return $colorCount;
	}

	/**
	 * Counts the color frequencies.
	 *
	 * @param int[] $pixels
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	private function countFrequencies (array $pixels): void
	{
		if (count($pixels) === 0)
			return;

		$currentColorIndex = 0;
		$currentColor = $pixels[0];

		$this->colors[$currentColorIndex] = $currentColor;
		$this->colorCounts[$currentColorIndex] = 1;

		if (count($pixels) === 1)
			return;

		for ($i = 1; $i < count($pixels); $i++)
		{
			if (count($this->colors) <= $currentColorIndex)
				continue;

			if ($pixels[$i] === $currentColor)
			{
				$this->colorCounts[$currentColorIndex]++;
			}
			else if (count($pixels) > $i)
			{
				$currentColor = $pixels[$i];
				$currentColorIndex++;

				if (count($this->colors) <= $currentColorIndex)
					break;

				$this->colors[$currentColorIndex] = $currentColor;
				$this->colorCounts[$currentColorIndex] = 1;
			}
		}
	}

}
