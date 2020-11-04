<?php
/**
 * Copyright (c) 2019 - 2020 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Image\GIF;

use Columba\Geometry\Point;
use Columba\Geometry\Rect;
use Columba\IO\Stream\Stream;

/**
 * Class GIFDecoder
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Image\GIF
 * @since 1.6.0
 */
class GIFDecoder
{

	public const GTC_NO = 0;
	public const GTC_FOLLOW = 1;

	public const SORT_NO = 0;
	public const SORT_IMPORTANCE = 1;

	protected Stream $stream;
	protected ?GIFFrame $currentFrame = null;
	protected int $globalColorTableFlag = self::GTC_NO;
	protected int $globalColorTableSize = 0;
	protected int $iterations = 0;
	protected ?Rect $screenSize = null;
	protected int $sortFlag = self::SORT_NO;

	/** @var int[] */
	protected array $buffer = [];

	/** @var int[] */
	protected array $globalColorTable = [];

	/** @var int[] */
	protected array $screen = [];

	/**
	 * GIFDecoder constructor.
	 *
	 * @param Stream $stream
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(Stream $stream)
	{
		$this->stream = $stream;
	}

	/**
	 * Decodes the GIF stream and calls the given callable on each frame.
	 *
	 * @param callable $onFrame
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function decode(callable $onFrame): void
	{
		$this->readHeader();
		$this->readLogicalScreenDescriptor();
		$this->readGlobalColorTable();
		$frameIndex = 0;
		$cycle = true;

		do
		{
			$this->readBytes(1);

			if (!$this->stream->hasReachedEOF())
			{
				switch ($this->buffer[0])
				{
					case 0x21:
						$this->readGraphicControlExtension();
						break;

					case 0x2C:
						$this->readImageDescriptor();
						$onFrame($frameIndex++, $this->currentFrame);
						break;

					case 0x3B:
						$cycle = false;
						break;
				}
			}
			else
			{
				$cycle = false;
			}
		}
		while ($cycle);

		$this->buffer = [];
		$this->currentFrame = null;
		$this->globalColorTable = [];
		$this->globalColorTableFlag = self::GTC_NO;
		$this->globalColorTableSize = 0;
		$this->sortFlag = self::SORT_NO;
		$this->screen = [];
	}

	/**
	 * Gets the screen size.
	 *
	 * @return Rect|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getScreenSize(): ?Rect
	{
		return $this->screenSize;
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
	 * Gets the amount of loop iterations.
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getLoopIterations(): int
	{
		return $this->iterations;
	}

	/**
	 * Reads the given amount of bytes.
	 *
	 * @param int $amount
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function readBytes(int $amount): void
	{
		$this->stream->readBytes($amount, $this->buffer);
	}

	/**
	 * Reads the global color table.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function readGlobalColorTable(): void
	{
		if ($this->globalColorTableFlag !== self::GTC_FOLLOW)
			return;

		$this->readBytes(3 * (2 << $this->globalColorTableSize));
		$this->globalColorTable = $this->buffer;
	}

	/**
	 * Reads the graphic
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function readGraphicControlExtension(): void
	{
		$this->readBytes(1);
		$switch = $this->buffer[0] === 0xFF;

		while (true)
		{
			$this->readBytes(1);

			if (($u = $this->buffer[0]) === 0x00)
				break;

			$this->readBytes($u);

			if ($switch)
			{
				if ($u === 0x03)
					$this->iterations = ($this->buffer[1] | $this->buffer[2] << 8);
			}
			else if ($u === 0x04)
			{
				$this->currentFrame = new GIFFrame();

				$packedFields = $this->buffer[0];
				$this->currentFrame->setDisposalMethod((isset($this->buffer[4]) ? $this->buffer[4] : 0) & 0x80 ? ($packedFields >> 2) - 1 : ($packedFields >> 2) - 0);
				$this->currentFrame->setDuration($this->toUnsignedShort($this->buffer, 1));
				$this->currentFrame->setIsTransparent(($packedFields & 0x1) === 0x1);

				if ($this->currentFrame->getIsTransparent())
				{
					$color = new GIFColor(-1, -1, -1);
					$color->setIndex($this->buffer[3]);

					$this->currentFrame->setTransparentColor($color);
				}
			}
		}
	}

	/**
	 * Reads the header.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function readHeader(): void
	{
		$this->readBytes(6); // [GIF89a, GIF87a]
	}

	/**
	 * Reads the image descriptor.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function readImageDescriptor(): void
	{
		$this->readBytes(9);

		$screen = $this->buffer;

		$this->currentFrame->setOffset(new Point($this->toUnsignedShort($screen, 0), $this->toUnsignedShort($screen, 2)));
		$this->currentFrame->setSize(new Rect($this->toUnsignedShort($screen, 4), $this->toUnsignedShort($screen, 6)));

		$globalColorTableFlag = ($screen[8] & 0x80) === 0x80;

		if ($globalColorTableFlag)
		{
			$code = $screen[8] & 0x07;
			$sort = $screen[8] & 0x20 ? 1 : 0;
		}
		else
		{
			$code = $this->globalColorTableSize;
			$sort = $this->sortFlag;
		}

		$size = 2 << $code;
		$this->screen[4] &= 0x70;
		$this->screen[4] |= 0x80;
		$this->screen[4] |= $code;

		if ($sort)
			$this->screen[4] |= 0x08;

		$stream = $this->currentFrame->getStream();
		$stream->writeString($this->currentFrame->getIsTransparent() ? 'GIF89a' : 'GIF87a');
		$stream->writeBytes($this->screen);

		$color = $this->currentFrame->getTransparentColor();

		if ($globalColorTableFlag)
		{
			$this->readBytes(3 * $size);

			if ($this->currentFrame->getIsTransparent())
			{
				$color->setR($this->buffer[3 * $color->getIndex() + 0]);
				$color->setG($this->buffer[3 * $color->getIndex() + 1]);
				$color->setB($this->buffer[3 * $color->getIndex() + 2]);
			}

			$stream->writeBytes($this->buffer);
		}
		else
		{
			if ($this->currentFrame->getIsTransparent())
			{
				$color->setR($this->globalColorTable[3 * $color->getIndex() + 0]);
				$color->setG($this->globalColorTable[3 * $color->getIndex() + 1]);
				$color->setB($this->globalColorTable[3 * $color->getIndex() + 2]);
			}

			$stream->writeBytes($this->globalColorTable);
		}

		if ($this->currentFrame->getIsTransparent())
			$stream->writeString("!\xF9\x04\x1\x0\x0" . chr($color->getIndex()) . "\x0");

		$stream->writeBytes([0x2C]);
		$screen[8] &= 0x40;
		$stream->writeBytes($screen);
		$this->readBytes(1);
		$stream->writeBytes($this->buffer);
		$srcPhpStream = $this->stream->getHandle();
		$dstPhpStream = $stream->getHandle();
		$blockSize = null;
		$blockSizeRaw = null;

		while (true)
		{
			$blockSizeRaw = fread($srcPhpStream, 1);
			$blockSize = ord($blockSizeRaw);
			fwrite($dstPhpStream, $blockSizeRaw);

			if ($blockSize === 0x00)
				break;

			fwrite($dstPhpStream, fread($srcPhpStream, $blockSize));
		}

		$stream->writeBytes([0x3B]);
	}

	/**
	 * Reads the logical screen descriptor.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function readLogicalScreenDescriptor(): void
	{
		$this->readBytes(7);

		$this->screenSize = new Rect(
			$this->toUnsignedShort($this->buffer, 0),
			$this->toUnsignedShort($this->buffer, 2)
		);

		$this->screen = $this->buffer;
		$this->globalColorTableFlag = $this->buffer[4] & 0x80 ? 1 : 0;
		$this->sortFlag = $this->buffer[4] & 0x08 ? 1 : 0;
		$this->globalColorTableSize = $this->buffer[4] & 0x07;
	}

	/**
	 * Converts the given offset of the given buffer to an unsigned short.
	 *
	 * @param array $buffer
	 * @param int $offset
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 *
	 * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
	 */
	protected function toUnsignedShort(array &$buffer, int $offset): int
	{
		return ($buffer[$offset] | $buffer[$offset + 1] << 8);
	}

}
