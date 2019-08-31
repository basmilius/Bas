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

use Columba\Database\MSSQLDatabaseDriver;
use function Columba\Util\pre;

require __DIR__ . '/../bootstrap-test.php';

header('Content-Type: text/plain');

$driver = new MSSQLDatabaseDriver('martijnw.com', 'basdb', 'bas', '', MSSQLDatabaseDriver::DRIVER_SQLSRV);

//preDie(
//	$driver
//		->select('*')
//		->from('user')
//		->limit(10)
//		->debug()['query']
//);

//$driver
//	->insertIntoValues(
//		'user',
//		['firstname', 'Bas', PDO::PARAM_STR],
//		['lastname', 'Milius', PDO::PARAM_STR],
//		['email', 'hello@bas.dev', PDO::PARAM_STR],
//		['city', 'Groenlo', PDO::PARAM_STR],
//		['country', 'The Netherlands', PDO::PARAM_STR]
//	)
//	->execute();

$users = executeAndPrint($driver
	->select('*')
	->from('user')
	->orderBy('userID')
	->limit(10, 100))
	->toArray();

pre($users);
