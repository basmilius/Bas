<?php
/**
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Image;

use InvalidArgumentException;

/**
 * Class Image
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Image
 * @since 1.1.0
 */
final class Image
{

	/**
	 * @var resource
	 */
	private $imageResource;

	/**
	 * @var int
	 */
	private $height;

	/**
	 * @var int
	 */
	private $width;

	/**
	 * Image constructor.
	 *
	 * @param resource $imageResource
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public function __construct($imageResource)
	{
		$this->imageResource = $imageResource;

		$this->height = imagesy($this->imageResource);
		$this->width = imagesx($this->imageResource);
	}

	/**
	 * Gets the color int at {@see $x} and {@see $y}.
	 *
	 * @param int $x
	 * @param int $y
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getColorIntAt(int $x, int $y): int
	{
		return imagecolorat($this->imageResource, $x, $y);
	}

	/**
	 * Gets the image resource.
	 *
	 * @return resource
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.2.0
	 */
	public final function getResource()
	{
		return $this->imageResource;
	}

	/**
	 * Gets the {@see $height} of the {@see Image}.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getHeight(): int
	{
		return $this->height;
	}

	/**
	 * Gets the {@see $width} of the {@see Image}.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function getWidth(): int
	{
		return $this->width;
	}

	/**
	 * Copies the {@see Image}.
	 *
	 * @return Image
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function copy(): Image
	{
		$resource = imagecreatetruecolor($this->width, $this->height);

		imagecopy($resource, $this->imageResource, 0, 0, 0, 0, $this->width, $this->height);

		return new Image($resource);
	}

	/**
	 * Destroys the {@see Image} resource.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function destroy(): void
	{
		imagedestroy($this->imageResource);
	}

	/**
	 * Resizes the {@see Image}.
	 *
	 * @param int  $width
	 * @param int  $height
	 * @param bool $crop
	 * @param bool $thumbnail
	 * @param bool $copy
	 *
	 * @return Image
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function resize(int $width, int $height, bool $crop = false, bool $thumbnail = false, bool $copy = false): Image
	{
		$image = $copy ? $this->copy() : $this;
		$oldResource = $image->imageResource;

		$ih = $height;
		$iw = $width;
		$x = 0;
		$y = 0;

		if ($crop && $image->width !== $image->height)
			if ($image->width > $image->height)
				$height = $ih = intval($image->height * ($height / $image->width));
			else
				$width = $iw = intval($image->width * ($width / $image->height));

		if ($thumbnail)
		{
			$hRatio = $height / $image->height;
			$wRatio = $width / $image->width;
			$ratio = max($hRatio, $wRatio);

			if ($ratio > 1.0)
				$ratio = 1.0;

			$ih = intval(floor($image->height * $ratio));
			$iw = intval(floor($image->width * $ratio));

			$x = intval(floor(($width - $iw) / 2));
			$y = intval(floor(($height - $ih) / 2));
		}

		$newResource = imagecreatetruecolor($width, $height);

		imagealphablending($newResource, false);
		imagesavealpha($newResource, true);

		imagecopyresampled($newResource, $oldResource, $x, $y, 0, 0, $iw, $ih, $image->width, $image->height);
		imagedestroy($oldResource);

		$image->height = imagesy($newResource);
		$image->width = imagesx($newResource);
		$image->imageResource = $newResource;

		return $image;
	}

	/**
	 * Rotates an image.
	 *
	 * @param int  $degrees
	 * @param bool $copy
	 *
	 * @return Image
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function rotate(int $degrees, bool $copy = false): Image
	{
		$image = $copy ? $this->copy() : $this;
		$transparent = imagecolorallocatealpha($image->imageResource, 0, 0, 0, 0);

		$newResource = imagerotate($image->imageResource, $degrees, $transparent);
		imagedestroy($image->imageResource);

		$image->imageResource = $newResource;

		return $image;
	}

	/**
	 * Prints the {@see Image}.
	 *
	 * @param string $type
	 * @param int    $quality
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function print(string $type = 'png', int $quality = 80): void
	{
		switch ($type)
		{
			case 'gif':
				header('Content-Type: image/gif');
				imagegif($this->imageResource);
				break;

			case 'jpg':
			case 'jpeg':
				header('Content-Type: image/jpeg');
				imagejpeg($this->imageResource, null, $quality);
				break;

			case 'png':
				header('Content-Type: image/png');
				imagepng($this->imageResource);
				break;

			case 'webp':
				header('Content-Type: image/webp');
				imagewebp($this->imageResource, null, $quality);
				break;
		}
	}

	/**
	 * Saves the {@see Image}.
	 *
	 * @param string $filename
	 * @param string $type
	 * @param int    $quality
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public final function save(string $filename, string $type = 'png', int $quality = 80): void
	{
		switch ($type)
		{
			case 'gif':
				imagegif($this->imageResource, $filename);
				break;

			case 'jpg':
			case 'jpeg':
				imagejpeg($this->imageResource, $filename, $quality);
				break;

			case 'png':
				imagepng($this->imageResource, $filename);
				break;

			case 'webp':
				imagewebp($this->imageResource, $filename, $quality);
				break;
		}
	}

	/**
	 * Creates an {@see Image} from file.
	 *
	 * @param string $fileName
	 *
	 * @return Image
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function fromFile(string $fileName): self
	{
		if (!is_file($fileName))
			throw new InvalidArgumentException('$fileName not found!');

		return new self(imagecreatefromstring(file_get_contents($fileName)));
	}

}
