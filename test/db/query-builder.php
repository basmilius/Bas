<?php
declare(strict_types=1);

use Columba\Database\MySQLDatabaseDriver;

require __DIR__ . '/../bootstrap-test.php';

header('Content-Type: text/plain');

$driver = new MySQLDatabaseDriver('dev_latte', '127.0.0.1', 3306, '', '');

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
