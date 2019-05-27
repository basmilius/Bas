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
use Columba\Database\QueryBuilder;
use function Columba\Util\pre;

require __DIR__ . '/../bootstrap-test.php';

header('Content-Type: text/plain');

$driver = new MySQLDatabaseDriver('dev_latte', '127.0.0.1', 3306, '', '');

$query = $driver->select()
	->from('user')
	->where('id', '>', 0)
	->parentheses(function (QueryBuilder $builder): void
	{
		$builder
			->and('is_active', '=', 1)
			->or('is_active', '=', 2);
	})
	->debug();

pre($query);
