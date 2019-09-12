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

use Columba\Foundation\DotEnv\DotEnv;
use function Columba\Util\dump;
use function Columba\Util\pre;

/** @noinspection PhpMultipleClassesDeclarationsInOneFile */

require_once __DIR__ . '/../../bootstrap-test.php';

header('Content-Type: text/plain');

$env = DotEnv::create(__DIR__);

pre($env->getVars());

dump(
	getenv('MY_API_KEY'),
	$_ENV,
	getenv()
);
