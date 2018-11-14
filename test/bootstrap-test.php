<?php
/**
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

use Columba\Autoloader;

require_once __DIR__ . '/../src/Columba/Autoloader.php';

function pre(...$data)
{
	echo '<pre>';
	print_r($data);
	echo '</pre>';
}

function pre_die(...$data)
{
	pre(...$data);
	die;
}

$autoloader = new Autoloader();
$autoloader->addDirectory(__DIR__ . '/../src', 'Columba\\');
$autoloader->register();
