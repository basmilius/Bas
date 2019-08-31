<?php
/**
 * Copyright (c) 2019 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

/** @noinspection PhpMultipleClassesDeclarationsInOneFile */

declare(strict_types=1);

use Columba\Data\TypedCollection;
use function Columba\Util\pre;

require __DIR__ . '/../bootstrap-test.php';

header('Content-Type: text/plain');

$collection = new TypedCollection;
$collection->append('a');
$collection->append('b');
$collection->append('c');
$collection->append('d');

$a = $collection->copy();
$b = $collection->copy();
$c = $collection->copy();
$d = $collection->copy();
$e = $a->merge($b);

$b[2] = 10;

pre($a, $b, $c, $d, $e);
