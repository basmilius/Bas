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

use Columba\Router\Middleware\AbstractMiddleware;
use Columba\Router\Response\HtmlResponse;
use Columba\Router\Route\AbstractRoute;
use Columba\Router\RouteContext;
use Columba\Router\Router;
use Columba\Router\RouterException;
use Columba\Router\SubRouter;

require_once __DIR__ . '/../bootstrap-test.php';

define('COLUMBA_ROUTER_AGRESSIVE_PROFILING', true);
header('Content-Type: text/plain');

class MyMiddleware extends AbstractMiddleware
{

	public function forContext(AbstractRoute $route, RouteContext $context, bool &$isValid): void
	{
		if (!$isValid)
			return;

//		$route->respond(sprintf('Override from middleware for: %s', $context->getFullPath()));
	}

}

class MyRouter extends Router
{

	public function __construct()
	{
		parent::__construct(new HtmlResponse());

		$this->use(MyMiddleware::class);

		$this->all('/sub', MySubRouter::class);

		$this->get('/', [$this, 'onGetIndex']);
		$this->get('/(profile|user)/$userId', [$this, 'onGetUser']);
		$this->get('/(profile|user)/$userId/invoices/$invoiceNo.(?<format>pdf|html)', [$this, 'onGetUserInvoice']);
		$this->get('/download/invoice.$format', [$this, 'onGetInvoice']);
		$this->get('/wildcard/*', [$this, 'onGetWildcard']);

		$this->get('/anonymous', function (RouteContext $context): void
		{
			print_r($context);
		});
	}

	public final function onGetIndex(): string
	{
		return 'Route: /';
	}

	public final function onGetInvoice(string $format): string
	{
		return 'Route: /download/invoice.' . $format;
	}

	public final function onGetUser(int $userId = 10): string
	{
		return 'Route: /user/' . $userId;
	}

	public final function onGetUserInvoice(bool $myBool, string $invoiceNo, string $format, int $userId = 10): string
	{
		return sprintf("Show invoice '%s' as '%s' for user %d AND %s.", $invoiceNo, $format, $userId, $myBool ? 'TRUE' : 'FALSE');
	}

	public final function onGetWildcard(RouteContext $context, string $wildcard): string
	{
		return $wildcard;
	}

}

class MySubRouter extends SubRouter
{

	public function __construct()
	{
		parent::__construct();

		$this->get('/', [$this, 'onGetIndex']);
	}

	public final function onGetIndex(): string
	{
		return 'Index of sub router';
	}

	public function onException(Exception $err, ?RouteContext $context = null)
	{
		pre_die(__METHOD__, func_get_args());
	}

}

try
{
	$router = new MyRouter();
	$router->define('myBool', true);
//	$router->execute('/profile/1/invoices/20191001.pdf', 'GET');
	$router->execute('/sub', 'GET');
}
catch (RouterException $err)
{
	print_r($err);
}

echo PHP_EOL;
