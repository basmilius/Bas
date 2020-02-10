<?php
/**
 * Copyright (c) 2019 - 2020 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

/** @noinspection PhpMultipleClassesDeclarationsInOneFile */

declare(strict_types=1);

use Columba\Foundation\Http\Request;
use function Columba\Util\preDie;

require_once __DIR__ . '/../../bootstrap-test.php';

header('Content-Type: text/plain');

$req = new Request();

preDie(
	$req
);
