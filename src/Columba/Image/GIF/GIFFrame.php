<?php
/**
 * Copyright (c) 2017 - 2019 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Latte Framework package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Image\GIF;

use Columba\Geometry\Point;
use Columba\Geometry\Rect;
use Columba\Image\Image;
use Columba\IO\Stream\MemoryStream;
use Columba\IO\Stream\Stream;

/**
 * Class GIFFrame
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Image\GIF
 * @since 1.6.0
 */
class GIFFrame
{

	public const DISPOSAL_UNKNOWN = 0;
	public const DISPOSAL_OFF = 1;
	public CONST DISPOSAL_RESTORE_BACKGROUND = 2;
	public CONST DISPOSAL_RESTORE_PREVIOUS = 3;

	protected Stream $stream;
	protected int $disposalMethod;
	protected int $duration;
	protected bool $isTransparent;
	protected GIFColor $transparentColor;
	protected Point $offset;
	protected Rect $size;

	/**
	 * GIFFrame constructor.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct()
	{
		$this->stream = new MemoryStream();
	}

	/**
	 * Gets the stream.
	 *
	 * @return Stream
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getStream(): Stream
	{
		return $this->stream;
	}

	/**
	 * Sets the stream.
	 *
	 * @param Stream $stream
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function setStream(Stream $stream): void
	{
		$this->stream = $stream;
	}

	/**
	 * Creates an {@see Image} instance with the image data of this frame.
	 *
	 * @return Image
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function createImage(): Image
	{
		return new Image(imagecreatefromstring($this->stream->getContents()));
	}

	/**
	 * Gets the disposal method.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getDisposalMethod(): int
	{
		return $this->disposalMethod;
	}

	/**
	 * Sets the disposal method.
	 *
	 * @param int $disposalMethod
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function setDisposalMethod(int $disposalMethod): void
	{
		$this->disposalMethod = $disposalMethod;
	}

	/**
	 * Gets the duration.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getDuration(): int
	{
		return $this->duration;
	}

	/**
	 * Sets the duration.
	 *
	 * @param int $duration
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function setDuration(int $duration): void
	{
		$this->duration = $duration;
	}

	/**
	 * Gets if this frame is transparent.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getIsTransparent(): bool
	{
		return $this->isTransparent;
	}

	/**
	 * Sets if this frame is transparent.
	 *
	 * @param bool $is
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function setIsTransparent(bool $is): void
	{
		$this->isTransparent = $is;
	}

	/**
	 * Gets the transparent color.
	 *
	 * @return GIFColor
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getTransparentColor(): GIFColor
	{
		return $this->transparentColor;
	}

	/**
	 * Sets the transparent color.
	 *
	 * @param GIFColor $transparentColor
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function setTransparentColor(GIFColor $transparentColor): void
	{
		$this->transparentColor = $transparentColor;
	}

	/**
	 * Gets the offset.
	 *
	 * @return Point
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getOffset(): Point
	{
		return $this->offset;
	}

	/**
	 * Sets the offset.
	 *
	 * @param Point $offset
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function setOffset(Point $offset): void
	{
		$this->offset = $offset;
	}

	/**
	 * Gets the size.
	 *
	 * @return Rect
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getSize(): Rect
	{
		return $this->size;
	}

	/**
	 * Sets the size.
	 *
	 * @param Rect $size
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function setSize(Rect $size): void
	{
		$this->size = $size;
	}

}
