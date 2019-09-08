<?php
/**
 * Copyright (c) 2019 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1, ticks=1);

use Columba\Autoloader;
use Columba\Database\QueryBuilder;
use Columba\Database\ResultSet;
use Columba\Error\ExceptionHandler;
use Columba\Foundation\System;
use function Columba\Util\pre;

require_once __DIR__ . '/../src/Columba/Autoloader.php';

function executeAndPrint(QueryBuilder $builder, string $which = 'query_raw'): ResultSet
{
	pre($builder->debug()[$which]);

	return $builder->execute();
}

$autoloader = new Autoloader();
$autoloader->register();

ExceptionHandler::register();

if (System::isCLI())
{
	$_SERVER['CONTENT_TYPE'] = '';
	$_SERVER['HTTP_USER_AGENT'] = 'ColumbaTestPage/1.6.0';
	$_SERVER['REMOTE_ADDR'] = '::1';
	$_SERVER['REQUEST_URI'] = '/';
}
