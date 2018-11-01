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

use Columba\Image\Image;

require_once __DIR__ . '/../bootstrap-test.php';

$image = Image::fromFile(__DIR__ . '/logo.png');
$image->resize(64, 64);
$image->print();
