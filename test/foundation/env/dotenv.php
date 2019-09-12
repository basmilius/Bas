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

/** @noinspection PhpMultipleClassesDeclarationsInOneFile */

require_once __DIR__ . '/../../bootstrap-test.php';

header('Content-Type: text/plain');

$env = DotEnv::create(__DIR__);

dump(
	'getenv',
	getenv('MY_API_KEY'),
	getenv('MY_QUOTED_STRING'),
	getenv('MY_CERTIFICATE')
);

dump(
	'$_ENV',
	$_ENV['MY_API_KEY'],
	$_ENV['MY_QUOTED_STRING'],
	$_ENV['MY_CERTIFICATE']
);
