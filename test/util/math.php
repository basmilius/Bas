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

use Columba\Util\MathUtil;

require_once __DIR__ . '/../bootstrap-test.php';

header('Content-Type: text/plain');

pre(
	MathUtil::ceilStep(16, 5),
	MathUtil::floorStep(16, 5),
	MathUtil::roundStep(17, 5),
	MathUtil::clamp(25, 10, 20)
);
