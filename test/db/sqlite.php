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

use Columba\Database\SQLiteDatabaseDriver;
use function Columba\Util\pre;
use function Columba\Util\preDie;

require __DIR__ . '/../bootstrap-test.php';

header('Content-Type: text/plain');

$dbFile = __DIR__ . '/db.sq3';
$driver = new SQLiteDatabaseDriver($dbFile);

//$driver->exec('CREATE TABLE IF NOT EXISTS messages (id INTEGER PRIMARY KEY, title TEXT, message TEXT, time INTEGER)');

//$driver
//	->insertInto('messages', 'title', 'message', 'time')
//	->values(
//		['Message ' . time(), PDO::PARAM_STR],
//		['Lorem ipsum dolor sit amet!!', PDO::PARAM_STR],
//		time()
//	)
//	->execute();

$results = $driver
	->select('*')
	->from('messages')
	->orderBy('id DESC')
	->execute()
	->toArray();

pre($results);

$driver->optimizeTable('messages')->execute();

preDie($driver);
