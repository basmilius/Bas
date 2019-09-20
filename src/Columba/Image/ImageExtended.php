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

namespace Columba\Image;

use Columba\Util\MathUtil;

/**
 * Class ImageExtended
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Image
 * @since 1.6.0
 */
class ImageExtended extends Image
{

	/**
	 * Changes the brightness of the image.
	 *
	 * @param int $level
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function brightness(int $level = 0): self
	{
		imagefilter($this->resource, IMG_FILTER_BRIGHTNESS, MathUtil::clamp($level, -255, 255));

		return $this;
	}

	/**
	 * Colorizes the image with the given color.
	 *
	 * @param int $red
	 * @param int $green
	 * @param int $blue
	 * @param int $alpha
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function colorize(int $red, int $green, int $blue, int $alpha = 0): self
	{
		imagefilter($this->resource, IMG_FILTER_COLORIZE, $red, $green, $blue, $alpha);

		return $this;
	}

	/**
	 * Changes the contrast of the image.
	 *
	 * @param int $level
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function contrast(int $level = 0): self
	{
		imagefilter($this->resource, IMG_FILTER_CONTRAST, MathUtil::clamp($level, -100, 100));

		return $this;
	}

	/**
	 * Detects edges of the image.
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function edgeDetect(): self
	{
		imagefilter($this->resource, IMG_FILTER_EDGEDETECT);

		return $this;
	}

	/**
	 * Embosses the image.
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function emboss(): self
	{
		imagefilter($this->resource, IMG_FILTER_EMBOSS);

		return $this;
	}

	/**
	 * Applies Gaussian Blur to the image.
	 *
	 * @param int $runs
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function gaussianBlur(int $runs = 1): self
	{
		for ($i = 0; $i < $runs; ++$i)
			imagefilter($this->resource, IMG_FILTER_GAUSSIAN_BLUR);

		return $this;
	}

	/**
	 * Grayscales the image.
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function grayscale(): self
	{
		imagefilter($this->resource, IMG_FILTER_GRAYSCALE);

		return $this;
	}

	/**
	 * Uses mean removal to achieve a "sketchy" effect.
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function meanRemoval(): self
	{
		imagefilter($this->resource, IMG_FILTER_MEAN_REMOVAL);

		return $this;
	}

	/**
	 * Reverses all colors of the image.
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function negate(): self
	{
		imagefilter($this->resource, IMG_FILTER_NEGATE);

		return $this;
	}

	/**
	 * Pixelates the image.
	 *
	 * @param int $pixelSize
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function pixelate(int $pixelSize): self
	{
		for ($y = 0; $y < $this->height; $y += $pixelSize)
		{
			for ($x = 0; $x < $this->width; $x += $pixelSize)
			{
				$rgb = imagecolorsforindex($this->resource, imagecolorat($this->resource, $x, $y));
				$color = imagecolorclosest($this->resource, $rgb['red'], $rgb['green'], $rgb['blue']);

				imagefilledrectangle($this->resource, $x, $y, $x + $pixelSize - 1, $y + $pixelSize - 1, $color);
			}
		}

		return $this;
	}

	/**
	 * Applies Selective Blur to the image.
	 *
	 * @param int $runs
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function selectiveBlur(int $runs = 1): self
	{
		for ($i = 0; $i < $runs; ++$i)
			imagefilter($this->resource, IMG_FILTER_SELECTIVE_BLUR);

		return $this;
	}

	/**
	 * Smoothens the image.
	 *
	 * @param int $level
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function smooth(int $level = 0): self
	{
		imagefilter($this->resource, IMG_FILTER_SMOOTH, MathUtil::clamp($level, -8, 8));

		return $this;
	}

}
