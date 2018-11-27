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
		$this->redirect('/redirect', '/destination');
		$this->redirect('/redirect/$postSlug(string)', '/destination/$postSlug');
		$this->redirect('/redirect/$userId(int)/$userSlug(string)', '/destination/$userId/$userSlug');
	}

	public final function onGetIndex(): string
	{
		return 'Route: /';
	}

	public final function onGetUser(int $userId): string
	{
		throw new Exception('Hi!');

		return 'Route: /user/' . $userId;
	}

}

try
{
	$router = new MyRouter();
	print_r($router->execute('/redirect/1/bas-milius', 'GET'));
}
catch (RouterException $err)
{
	print_r($err);
}
