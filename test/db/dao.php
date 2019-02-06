<?php
/** @noinspection PhpMultipleClassesDeclarationsInOneFile */

declare(strict_types=1);

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

$user = User::get(1);

pre($user);
