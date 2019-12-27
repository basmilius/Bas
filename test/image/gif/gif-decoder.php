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

use Columba\Image\GIF\GIFDecoder;
use Columba\Image\GIF\GIFFrame;
use Columba\Image\GIF\GIFRenderer;
use Columba\Image\Image;
use Columba\IO\Stream\FileStream;

/*
 * This test was made in basmilius/latte and doesn't yet work with the
 * Columba test system.
 */

require_once __DIR__ . '/../../src/bootstrap.php';

$example = __DIR__ . DIRECTORY_SEPARATOR . 'niece.gif';
$exampleDir = __DIR__ . DIRECTORY_SEPARATOR . 'niece';

$stream = new FileStream($example);
$decoder = new GIFDecoder($stream);
$renderer = new GIFRenderer($decoder);

$renderer->run(function (int $index, GIFFrame $frame, Image $image) use ($exampleDir): void
{
	$name = $exampleDir . '/frame-' . str_pad(strval($index), 3, '0', STR_PAD_LEFT) . '.gif';

	$image = $image->copy();
	$image->resize(100, 100, Image::COVER);
	$image->gif($name);
});
