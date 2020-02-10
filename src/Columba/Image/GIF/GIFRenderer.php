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

use Columba\Image\Image;

/**
 * Class GIFRenderer
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Image\GIF
 * @since 1.6.0
 */
class GIFRenderer
{

	protected GIFDecoder $decoder;
	protected ?Image $currentFrame = null;
	protected ?GIFFrame $previousFrame = null;

	/**
	 * GIFRenderer constructor.
	 *
	 * @param GIFDecoder $decoder
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(GIFDecoder $decoder)
	{
		$this->decoder = $decoder;
	}

	/**
	 * Iterates to each frame and passes the rendered image to the given callback.
	 *
	 * @param callable $onRender
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function run(callable $onRender): void
	{
		$this->decoder->decode(fn(int $index, GIFFrame $frame) => $onRender($index, $frame, $this->render($frame, $index)));
	}

	/**
	 * Renders a single frame.
	 *
	 * @param GIFFrame $frame
	 * @param int      $index
	 *
	 * @return Image
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function render(GIFFrame $frame, int $index): Image
	{
		if ($index === 0)
		{
			$screenSize = $this->decoder->getScreenSize();

			$image = new Image(imagecreatetruecolor($screenSize->getWidth(), $screenSize->getHeight()));
			$image->with(function ($resource): void
			{
				imagealphablending($resource, false);
				imagesavealpha($resource, true);

				$transparentColor = imagecolortransparent($resource, imagecolorallocatealpha($resource, 255, 255, 255, 127));
				imagefill($resource, 0, 0, $transparentColor);
			});

			$this->currentFrame = $image;
			$this->previousFrame = $frame;
			$this->copyFrameToBuffer($frame);

			return $this->currentFrame;
		}

		$this->currentFrame->with(function ($resource) use ($frame): void
		{
			imagepalettetotruecolor($resource);
			$disposalMethod = $this->previousFrame->getDisposalMethod();

			if ($disposalMethod === 0 || $disposalMethod === 1)
			{
				$this->copyFrameToBuffer($frame);
			}
			else if ($disposalMethod === 2)
			{
				$this->restoreToBackground($this->previousFrame, imagecolortransparent($resource));
				$this->copyFrameToBuffer($frame);
			}
			else
			{
				throw new GIFException(sprintf('The disposal method %d is not implemented.', $disposalMethod), GIFException::ERR_NOT_IMPLEMENTED);
			}
		});

		$this->previousFrame = $frame;

		return $this->currentFrame;
	}

	/**
	 * Copies the given frame to the render buffer.
	 *
	 * @param GIFFrame $frame
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function copyFrameToBuffer(GIFFrame $frame): void
	{
		$this->currentFrame->with(function ($currentResource) use ($frame): void
		{
			$offset = $frame->getOffset();
			$size = $frame->getSize();

			$image = $frame->createImage();
			$image->with(function ($frameResource) use ($currentResource, $offset, $size): void
			{
				imagecopy(
					$currentResource,
					$frameResource,
					$offset->getX(),
					$offset->getY(),
					0,
					0,
					$size->getWidth(),
					$size->getHeight()
				);
			});

			$image->destroy();
		});
	}

	/**
	 * Restores the background color.
	 *
	 * @param GIFFrame $frame
	 * @param int      $backgroundColor
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function restoreToBackground(GIFFrame $frame, int $backgroundColor): void
	{
		$this->currentFrame->with(function ($currentResource) use ($frame, $backgroundColor): void
		{
			$offset = $frame->getOffset();
			$size = $frame->getSize();

			imagefilledrectangle(
				$currentResource,
				$offset->getX(),
				$offset->getY(),
				$offset->getX() + $size->getWidth() - 1,
				$offset->getY() + $size->getHeight() - 1,
				$backgroundColor
			);
		});
	}

}
