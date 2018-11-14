<?php
declare(strict_types=1);

/**
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

use Columba\Minecraft\Server;

require_once __DIR__ . '/../bootstrap-test.php';

try
{
	$server = new Server('hub.mcs.gg', 25565, 5, false);
	$server->connect();

	pre_die($server->query());
}
catch (Exception $err)
{
	pre_die($err);
}
