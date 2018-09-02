<?php
declare(strict_types=1);

use Columba\Router\Response\HtmlResponse;
use Columba\Router\Router;
use Columba\Router\RouterException;

require_once __DIR__ . '/../bootstrap-test.php';

header('Content-Type: text/plain');

class MyRouter extends Router
{

	public function __construct()
	{
		parent::__construct();

		$this->get('/', [$this, 'onGetIndex']);
		$this->get('/user/$userId', [$this, 'onGetUser']);
	}

	public final function onGetIndex(): string
	{
		return 'Route: /';
	}

	public final function onGeetUser(int $userId): string
	{
		return 'Route: /user/' . $userId;
	}

}

try
{
	$router = new MyRouter();
	print_r($router->execute('/user/3', 'GET'));
}
catch(RouterException $err)
{
	print_r($err);
}
