<?php
declare(strict_types=1);

use Bas\Http\Http;

require_once __DIR__ . '/../../src/Bas/Bas.php';
require_once __DIR__ . '/../../src/Bas/Http/Http.php';
require_once __DIR__ . '/../../src/Bas/Http/HttpException.php';
require_once __DIR__ . '/../../src/Bas/Http/HttpUtil.php';
require_once __DIR__ . '/../../src/Bas/Http/Request.php';
require_once __DIR__ . '/../../src/Bas/Http/RequestMethod.php';
require_once __DIR__ . '/../../src/Bas/Http/Response.php';
require_once __DIR__ . '/../../src/Bas/Util/ArrayUtil.php';

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
