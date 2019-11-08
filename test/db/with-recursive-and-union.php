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

require __DIR__ . '/../bootstrap-test.php';

header('Content-Type: text/plain');

$driver = new MySQLDatabaseDriver('127.0.0.1', 'latte', 'root', '');

$query = $driver
	->withRecursive('cte1', $driver
		->select('folder.*')
		->from('folder')
		->where('folder.id', '=', 1)
		->unionAll($driver
			->select('folder.*')
			->from('folder', 'cte1')
			->where('folder.parent_id', '=', 'cte1.id')))
	->select('*')
	->from('cte1')
	->unionAll($driver->select('*')->from('cte1'));

$debug = $query->debug();

echo $debug['query_raw'], PHP_EOL, PHP_EOL;

print_r($query->execute()->toArray());
