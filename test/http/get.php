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

use Columba\Http\Http;

require_once __DIR__ . '/../../src/Columba/Columba.php';
require_once __DIR__ . '/../../src/Columba/Http/Http.php';
require_once __DIR__ . '/../../src/Columba/Http/HttpException.php';
require_once __DIR__ . '/../../src/Columba/Http/HttpUtil.php';
require_once __DIR__ . '/../../src/Columba/Http/Request.php';
require_once __DIR__ . '/../../src/Columba/Http/RequestMethod.php';
require_once __DIR__ . '/../../src/Columba/Http/Response.php';
require_once __DIR__ . '/../../src/Columba/Util/ArrayUtil.php';

header('Content-Type: text/plain');

try
{
	$http = new Http();
	$response = $http->get('https://intranet.atbm.nl/login', null, $request);

	print_r($response);
}
catch (Exception $err)
{
	print_r($err->getMessage());
}
