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

use Columba\Database\PostgreSQLDatabaseDriver;
use function Columba\Util\preDie;

require __DIR__ . '/../bootstrap-test.php';
header('Content-Type: text/plain');

$driver = new PostgreSQLDatabaseDriver('localhost', 'columba_test', 'dev', '');

preDie(
	$driver
		->select('*')
		->from('user')
		->orderBy('firstname')
		->execute()
		->collection()
);
