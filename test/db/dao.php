<?php
/**
 * Copyright (c) 2019 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

/** @noinspection PhpMultipleClassesDeclarationsInOneFile */

declare(strict_types=1);

use Columba\Database\Dao\Model;
use Columba\Database\MySQLDatabaseDriver;
use function Columba\Util\pre;

require __DIR__ . '/../bootstrap-test.php';

header('Content-Type: text/plain');

class Country extends Model
{

}

class File extends Model
{

}

class User extends Model
{

	protected static $mappings = [
		'address_country_id' => Country::class,
		'photo_file_id' => File::class
	];

}

$driver = new MySQLDatabaseDriver('127.0.0.1', 'latte', 'root', '');

Model::init($driver);

$query = User::select('SQL_CALC_FOUND_ROWS')
	->where('id', '=', 1);

$result = executeAndPrint($query);

pre(
	$result->isEmpty(),
	$result->hasOne(),
	$result->hasAtLeast(1),
	$result->rowCount(),
	$result->affectedRows(),
	$result->foundRows()
);

foreach ($result as $user)
{
	pre($user);
}

$result->rewind();

pre($result->model());
