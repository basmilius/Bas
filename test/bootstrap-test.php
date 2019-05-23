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

use Columba\Autoloader;
use Columba\Database\QueryBuilder;
use Columba\Database\ResultSet;
use Columba\Error\ExceptionHandler;
use Columba\Util\ArrayUtil;

require_once __DIR__ . '/../src/Columba/Autoloader.php';

function executeAndPrint(QueryBuilder $builder, string $which = 'query_raw'): ResultSet
{
	pre($builder->debug()[$which]);

	return $builder->execute();
}

function pre(...$data)
{
	$shouldEcho = php_sapi_name() !== 'cli' && !in_array('Content-type: text/plain;charset=UTF-8', headers_list());

	if (count($data) === 1 && ArrayUtil::isSequentialArray($data))
		$data = $data[0];

	if ($shouldEcho)
		echo '<pre>';

	print_r($data);
	echo PHP_EOL;

	if ($shouldEcho)
		echo '</pre>';
}

function pre_die(...$data)
{
	pre(...$data);
	die;
}

$autoloader = new Autoloader();
$autoloader->register();

ExceptionHandler::register();
