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

use Columba\Palette\Palette;
use Columba\Util\MathUtil;
use Exception;
use Generator;
use InvalidArgumentException;

/**
 * Class Image
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Image
 * @since 1.1.0
 */
class Image
{

	public const BICUBIC = IMG_BICUBIC;
	public const BICUBIC_FIXED = IMG_BICUBIC_FIXED;
	public const BILINEAR_FIXED = IMG_BILINEAR_FIXED;
	public const NEAREST_NEIGHBOUR = IMG_NEAREST_NEIGHBOUR;

	public const CONTAIN = 1;
	public const COVER = 2;
	public const FILL = 4;

	/**
	 * @var resource
	 */
	protected $resource;

	/**
	 * @var int
	 */
	protected $xDpi;

	/**
	 * @var int
	 */
	protected $yDpi;

	/**
	 * @var int
	 */
	protected $height;

	/**
	 * @var int
	 */
	protected $width;

	/**
	 * Image constructor.
	 *
	 * @param resource $resource
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public function __construct($resource)
	{
		$this->resource = $resource;
		$this->updateSizeInfo();
	}

	/**
	 * Updates the current used image resource.
	 *
	 * @param resource $resource
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private function updateResource($resource): void
	{
		imagedestroy($this->resource);
		$this->resource = $resource;

		imagealphablending($this->resource, false);
		imagesavealpha($this->resource, true);

		$this->updateSizeInfo();
	}

	/**
	 * Updates size information with the current image resource.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	private function updateSizeInfo(): void
	{
		[$this->xDpi, $this->yDpi] = imageresolution($this->resource);
		$this->height = imagesy($this->resource);
		$this->width = imagesx($this->resource);
	}

	/**
	 * Fixes the image from exif data.
	 *
	 * @param array $exif
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function fixFromExif(array $exif): self
	{
		$orientation = $exif['Orientation'] ?? 1;

		$flip = false;
		$rotation = 0;

		switch ($orientation)
		{
			case 8:
				$rotation = 90;
				break;

			case 3:
				$rotation = 180;
				break;

			case 6:
				$rotation = 270;
				break;

			case 2:
				$flip = true;
				break;

			case 7:
				$flip = true;
				$rotation = 90;
				break;

			case 4:
				$flip = true;
				$rotation = 180;
				break;

			case 5:
				$flip = true;
				$rotation = 270;
				break;
		}

		if ($rotation > 0)
			$this->rotate($rotation);

		if ($flip)
			$this->flip(false, true);

		return $this;
	}

	/**
	 * Gets the DPI of the image.
	 *
	 * @return int[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getDpi(): array
	{
		return [$this->xDpi, $this->yDpi];
	}

	/**
	 * Gets the height of th eimage.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public function getHeight(): int
	{
		return $this->height;
	}

	/**
	 * Gets the width of the image.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public function getWidth(): int
	{
		return $this->width;
	}

	/**
	 * Gets size and resolution information of this image.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getSize(): array
	{
		return [$this->width, $this->height, $this->xDpi, $this->yDpi];
	}

	/**
	 * Creates a {@see Palette} instance.
	 *
	 * @return Palette
	 * @throws Exception
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getPalette(): Palette
	{
		return Palette::generate($this);
	}

	/**
	 * Takes a piece from the image and returns a new {@see Image} instance.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $width
	 * @param int $height
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getPiece(int $x, int $y, int $width, int $height): self
	{
		$image = $this->copy();
		$image->piece($x, $y, $width, $height);

		return $image;
	}

	/**
	 * Gets a pixel.
	 *
	 * @param int $x
	 * @param int $y
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getPixel(int $x, int $y): int
	{
		if ($x < 0 || $x > $this->width || $y < 0 || $y > $this->height)
			throw new InvalidArgumentException(sprintf('Coordinate %dx%x is out of bounds.', $x, $y));

		return imagecolorat($this->resource, $x, $y);
	}

	/**
	 * Iterate over all pixels.
	 *
	 * @return Generator<int>
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getPixels(): Generator
	{
		for ($y = 0; $y < $this->height; $y++)
			for ($x = 0; $x < $this->width; $x++)
				yield $this->getPixel($x, $y);
	}

	/**
	 * Executes a function with the image resource.
	 *
	 * @param callable $fn
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function with(callable $fn): void
	{
		$fn($this->resource);
	}

	/**
	 * Copies the image.
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public function copy(): self
	{
		$resource = imagecreatetruecolor($this->width, $this->height);

		imagecopy($resource, $this->resource, 0, 0, 0, 0, $this->width, $this->height);

		return new self($resource);
	}

	/**
	 * Destroys the image resource.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public function destroy(): void
	{
		imagedestroy($this->resource);
	}

	/**
	 * Flips the image.
	 *
	 * @param bool $horizontal
	 * @param bool $vertical
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function flip(bool $horizontal, bool $vertical): self
	{
		if ($horizontal || $vertical)
			imageflip($this->resource, $horizontal && $vertical ? IMG_FLIP_BOTH : ($horizontal ? IMG_FLIP_HORIZONTAL : IMG_FLIP_VERTICAL));

		return $this;
	}

	/**
	 * Takes a piece from the image.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $width
	 * @param int $height
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function piece(int $x, int $y, int $width, int $height): self
	{
		$this->updateResource(imagecrop($this->resource, [
			'x' => $x,
			'y' => $y,
			'width' => $width,
			'height' => $height
		]));

		return $this;
	}

	/**
	 * Resizes the image.
	 *
	 * @param int $width
	 * @param int $height
	 * @param int $mode
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function resize(int $width, int $height, int $mode = self::FILL): self
	{
		$resource = imagecreatetruecolor($width, $height);
		imagefill($resource, 0, 0, imagecolorallocatealpha($resource, 0, 0, 0, 127));

		$rx = $width / $this->width;
		$ry = $height / $this->height;
		$r = $this->width <= $this->height ? $rx : $ry;

		switch ($mode)
		{
			case self::CONTAIN:
				if ($r * $this->width > $width)
					$r = $rx;

				if ($r * $this->height > $height)
					$r = $ry;
				break;

			case self::COVER:
				if ($r * $this->width < $width)
					$r = $rx;

				if ($r * $this->height < $height)
					$r = $ry;
				break;
		}

		$dw = $mode === self::FILL ? $width : (int)round($this->width * $r);
		$dh = $mode === self::FILL ? $height : (int)round($this->height * $r);
		$dx = $mode === self::FILL ? 0 : (int)round(($dw - $width) / -2);
		$dy = $mode === self::FILL ? 0 : (int)round(($dh - $height) / -2);

		imagecopyresampled($resource, $this->resource, $dx, $dy, 0, 0, $dw, $dh, $this->width, $this->height);

		$this->updateResource($resource);

		return $this;
	}

	/**
	 * Rotates the image.
	 *
	 * @param int $degrees
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function rotate(int $degrees): self
	{
		$transparent = imagecolorallocatealpha($this->resource, 0, 0, 0, 127);
		$this->updateResource(imagerotate($this->resource, $degrees, $transparent));

		return $this;
	}

	/**
	 * Scales the image.
	 *
	 * @param float $scale
	 * @param int   $mode
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function scale(float $scale, int $mode = self::BILINEAR_FIXED): self
	{
		$this->updateResource(imagescale($this->resource, (int)round($this->width * $scale), -1, $mode));

		return $this;
	}

	/**
	 * Creates an image in the GIF format.
	 *
	 * @param string|null $fileName
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function gif(?string $fileName = null): bool
	{
		return imagegif($this->resource, $fileName);
	}

	/**
	 * Creates an image in the JPEG format.
	 *
	 * @param string|null $fileName
	 * @param int         $quality
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function jpeg(?string $fileName = null, int $quality = 75): bool
	{
		return imagejpeg($this->resource, $fileName, MathUtil::clamp($quality, 0, 100));
	}

	/**
	 * Creates an image in the PNG format.
	 *
	 * @param string|null $fileName
	 * @param int         $compression
	 * @param int         $filters
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function png(?string $fileName = null, int $compression = 9, int $filters = -1): bool
	{
		return imagepng($this->resource, $fileName, $compression, $filters);
	}

	/**
	 * Creates an image in the WebP format.
	 *
	 * @param string|null $fileName
	 * @param int         $quality
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function webp(?string $fileName = null, int $quality = 80): bool
	{
		return imagewebp($this->resource, $fileName, $quality);
	}

	/**
	 * Creates an image in the given format.
	 *
	 * @param string      $format
	 * @param string|null $fileName
	 * @param mixed       ...$arguments
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function save(string $format, ?string $fileName = null, ...$arguments): bool
	{
		switch ($format)
		{
			case 'gif':
				return $this->gif($fileName);

			case 'jpg':
			case 'jpeg':
				return $this->jpeg($fileName, ...$arguments);

			case 'png':
				return $this->png($fileName, ...$arguments);

			case 'webp':
				return $this->webp($fileName, ...$arguments);

			default:
				throw new InvalidArgumentException(sprintf('Image type "%s" is not supported.', $format));
		}
	}

	/**
	 * Creates an {@see Image} instance from file.
	 *
	 * @param string $fileName
	 *
	 * @return static
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function fromFile(string $fileName): self
	{
		if (!is_file($fileName))
			new InvalidArgumentException(sprintf('The file "%s" does not exists.', $fileName));

		return new static(imagecreatefromstring(file_get_contents($fileName)));
	}

}
