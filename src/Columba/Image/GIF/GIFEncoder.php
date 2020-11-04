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

use Columba\IO\Stream\MemoryStream;
use Columba\IO\Stream\Stream;

/**
 * Class GIFEncoder
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Image\GIF
 * @since 1.6.0
 */
class GIFEncoder
{

	protected Stream $output;
	protected int $iterations;
	protected bool $headerInjected = false;
	protected ?string $firstFrameBytes = null;
	protected int $frameIndex = 0;
	protected ?string $globalColorTable = null;
	protected ?int $globalColorTableSize = null;

	/**
	 * GIFEncoder constructor.
	 *
	 * @param Stream $output
	 * @param int $iterations
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(Stream $output, int $iterations = 0)
	{
		$this->output = $output;
		$this->iterations = $iterations;
	}

	/**
	 * Gets the output stream.
	 *
	 * @return Stream
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getOutput(): Stream
	{
		return $this->output;
	}

	/**
	 * Adds the GIF footer.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function addFooter(): void
	{
		$this->output->writeString(";");
	}

	/**
	 * Adds the given frame.
	 *
	 * @param GIFFrame $frame
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function addFrame(GIFFrame $frame)
	{
		$bytes = $frame->getStream()->getContents();
		$this->checkAnimated($bytes, $this->frameIndex);

		if (!$this->headerInjected)
			$this->addHeader($frame);

		$tcolor = $frame->getTransparentColor();

		$localPackedFields = ord($bytes[10]);
		$localColorTableSize = 3 * (2 << ($localPackedFields & 0x07));
		$localColorTable = substr($bytes, 13, $localColorTableSize);
		$imgData = substr($bytes, 13 + $localColorTableSize, -1);

		$localGCE = new MemoryStream();
		$localGCE->writeBytes([0x21, 0xF9, 0x04, ($frame->getDisposalMethod() << 2) + 0]);
		$localGCE->writeString($this->toUnsignedShort($frame->getDuration()));
		$localGCE->writeBytes([0x00, 0x00]);

		if ($tcolor instanceof GIFColor && $localPackedFields & 0x80)
		{
			for ($j = 0; $j < $localColorTableSize / 3; $j++)
			{
				if (ord($localColorTable[3 * $j + 0]) === ($tcolor->getR() & 0xFF) && ord($localColorTable[3 * $j + 1]) === ($tcolor->getG() & 0xFF) && ord($localColorTable[3 * $j + 2]) === ($tcolor->getB() & 0xFF))
					continue;

				$localGCE->seek(3);
				$localGCE->writeBytes([($frame->getDisposalMethod() << 2) + 1]);
				$localGCE->seek(6);
				$localGCE->writeBytes([$j]);
				break;
			}
		}

		switch (ord($imgData[0]))
		{
			case 0x21:
//				$gce = substr($imgData, 0, 8);
//				$gcePackedFields = $gce[3];
//				$gceTransparencyFlag = (ord($gcePackedFields) & 0x01) == 0x01;
//				$gceTransparencyIndex = ord($gce[6]);
				$imgDescriptor = substr($imgData, 8, 10);
				$imgData = substr($imgData, 18);
				break;

			case 0x2C:
				$imgDescriptor = substr($imgData, 0, 10);
				$imgData = substr($imgData, 10);
				break;

			default:
				throw new GIFException('Unexpected input.', GIFException::ERR_UNEXPECTED);
		}

		$this->output->writeString($localGCE->getContents());
		$applyLocalColorTable = $localPackedFields & 0x80 && $this->headerInjected && ($this->globalColorTableSize != $localColorTableSize || !$this->blockCompare($localColorTable));

		if ($applyLocalColorTable)
		{
			$byte = ord($imgDescriptor[9]);
			$byte |= 0x80;
			$byte &= 0xF8;
			$byte |= (ord($this->firstFrameBytes[10]) & 0x07);
			$imgDescriptor[9] = chr($byte);
			$this->output->writeString($imgDescriptor . $localColorTable);
		}
		else
		{
			$this->output->writeString($imgDescriptor);
		}

		$this->output->writeString($imgData);
		$this->frameIndex++;
	}

	/**
	 * Adds the GIF header.
	 *
	 * @param GIFFrame $firstFrame
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function addHeader(GIFFrame $firstFrame): void
	{
		$this->firstFrameBytes = $firstFrame->getStream()->getContents();
		$this->output->writeString('GIF89a');
		$packedFields = ord($this->firstFrameBytes[10]);

		if ($globalColorTableFlag = $packedFields & 0x80)
		{
			$this->globalColorTableSize = 3 * (2 << ($packedFields & 0x07));
			$this->globalColorTable = substr($this->firstFrameBytes, 13, $this->globalColorTableSize);
			$this->output->writeString(substr($this->firstFrameBytes, 6, 7) . $this->globalColorTable . "!\377\13NETSCAPE2.0\3\1" . $this->toUnsignedShort($this->iterations) . "\0");
		}

		$this->headerInjected = true;
	}

	/**
	 * Compares the local block with the global color table.
	 *
	 * @param string $localBlock
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function blockCompare(string $localBlock): bool
	{
		$globalBlock = $this->globalColorTable;
		$len = $this->globalColorTableSize;

		for ($i = 0; $i < $len; $i++)
			if ($globalBlock[$i] !== $localBlock[$i])
				return false;

		return true;
	}

	/**
	 * Checks if the given frame index is animated or something, idk, was needed..!
	 *
	 * @param string $bytes
	 * @param int $index
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 *
	 * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
	 */
	protected function checkAnimated(string &$bytes, int $index): void
	{
		$packedFields = ord($bytes[10]);
		$globalColorTableSize = 3 * (2 << ($packedFields & 0x07));

		for ($j = (13 + $globalColorTableSize), $k = true; $k; $j++)
		{
			switch ($bytes[$j])
			{
				case "!":
					if ((substr($bytes, $j + 3, 8)) == "NETSCAPE")
						throw new GIFException(sprintf('Cannot create frame from animated resource #%d', $index), GIFException::ERR_INVALID_FRAME);

					break;

				case ";":
					$k = false;
					break;
			}
		}
	}

	/**
	 * Converts the given integer to an unsigned short string.
	 *
	 * @param int $num
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function toUnsignedShort(int $num): string
	{
		return chr($num & 0xFF) . chr(($num >> 8) & 0xFF);
	}

}
