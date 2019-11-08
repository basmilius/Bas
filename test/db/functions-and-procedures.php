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

use Columba\Database\MySQLDatabaseDriver;
use function Columba\Util\pre;

require __DIR__ . '/../bootstrap-test.php';

header('Content-Type: text/plain');

$appId = 3;
$driver = new MySQLDatabaseDriver('127.0.0.1', 'latte', 'root', '');

pre(
	$driver->callFunction('HUB_GET_EVENTS_COUNT', $appId, 'install', strtotime('- 1 year'), time()),
	$driver->callFunction('HUB_GET_EVENTS_COUNT', $appId, 'install', strtotime('- 2 years'), strtotime('- 1 year'))
);

$output = [
	'total' => PDO::PARAM_INT,
	'active' => PDO::PARAM_INT
];
$driver->executeProcedure('HUB_APP_INSTALLATIONS', [3], $output);

pre($output);
