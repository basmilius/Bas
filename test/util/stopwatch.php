<?php
/**
 * Copyright (c) 2019 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

use Columba\Util\Stopwatch;
use function Columba\Util\pre;

require_once __DIR__ . '/../bootstrap-test.php';

header('Content-Type: text/plain');

Stopwatch::start('a');
Stopwatch::stop('a', $time, Stopwatch::UNIT_SECONDS);

pre(number_format($time, 15, ',', '.') . ' seconds');
