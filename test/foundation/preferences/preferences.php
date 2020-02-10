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

use Columba\Foundation\Preferences\Preferences;
use function Columba\Util\dumpDie;

require_once __DIR__ . '/../../bootstrap-test.php';

header('Content-Type: text/plain');

$preferences = Preferences::loadFromJson(__DIR__ . '/preferences.json');

dumpDie(
	$preferences['enable_development_mode'],
	$preferences['db']['username'],
	$preferences['invalid_key']
);
