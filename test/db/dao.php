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

use Columba\Data\Collection;
use Columba\Database\MySQLDatabaseDriver;
use Columba\Database\Dao\Model;

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

$driver = new MySQLDatabaseDriver('dev_latte', '127.0.0.1', 3306, 'dev', '');

Model::init($driver);

$collection = new Collection(User::all());
$collection = $collection->map(function (User $user): string
{
	return $user['id'] . ' ' . $user['name'];
});

pre($collection);
