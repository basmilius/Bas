<?php
declare(strict_types=1);

use Columba\Image\Image;

require_once __DIR__ . '/../bootstrap-test.php';

$image = Image::fromFile(__DIR__ . '/logo.png');
$image->resize(64, 64);
$image->print();
