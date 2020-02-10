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

use Columba\Image\GIF\GIFColor;
use Columba\Image\GIF\GIFEncoder;
use Columba\Image\GIF\GIFFrame;
use Columba\IO\Stream\FileStream;
use Columba\IO\Stream\MemoryStream;

/*
 * This test was made in basmilius/latte and doesn't yet work with the
 * Columba test system.
 */

require_once __DIR__ . '/../../src/bootstrap.php';

$exampleDir = __DIR__ . DIRECTORY_SEPARATOR . 'niece';

$encoder = new GIFEncoder(new MemoryStream());

foreach (glob($exampleDir . '/frame-*.gif') as $fileName)
{
	$frame = new GIFFrame();
	$frame->setDisposalMethod(GIFFrame::DISPOSAL_OFF);
	$frame->setDuration(5);
	$frame->setTransparentColor(new GIFColor(255, 255, 255));
	$frame->setStream(new FileStream($fileName));

	$encoder->addFrame($frame);
}

$encoder->addFooter();

header('Content-Type: image/gif');
echo $encoder->getOutput()->getContents();
