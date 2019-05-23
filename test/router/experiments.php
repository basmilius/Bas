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

use Columba\Http\Foundation\Request;
use Columba\Router\Middleware\AbstractMiddleware;
use Columba\Router\Renderer\DebugRenderer;
use Columba\Router\Response\HtmlResponse;
use Columba\Router\Response\JsonResponse;
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
		parent::__construct(new HtmlResponse(), new DebugRenderer());

		$this->use(MyMiddleware::class);

		$this->all('/sub', MySubRouter::class);

		$this->get('/', [$this, 'onGetIndex']);
		$this->get('/(profile|user)/$userId', [$this, 'onGetUser']);
		$this->get('/(profile|user)/$userId/invoices/$invoiceNo.(?<format>pdf|html)', [$this, 'onGetUserInvoice']);
		$this->get('/download/invoice.$format', [$this, 'onGetInvoice']);
		$this->get('/wildcard/*', [$this, 'onGetWildcard']);

		$this->get('/anonymous', function (RouteContext $context): RouteContext
		{
			return $context;
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

	public function onNotFound(string $requestPath, RouteContext $context): bool
	{
		pre(__METHOD__, 'Route was not found, not found handled in MyRouter.', $requestPath, $context->getResponseImplementation());

		return true;
	}

}

class MySubRouter extends SubRouter
{

	public function __construct()
	{
		parent::__construct(new JsonResponse());

		$this->get('/', [$this, 'onGetIndex']);
		$this->get('/$name', [$this, 'onGetName']);
	}

	public final function onGetIndex(RouteContext $context): string
	{
		return $context->getFullPath();
	}

	public final function onGetName(Request $request, RouteContext $context, string $name): array
	{
		return [
			'request' => $request,
			'path' => $context->getFullPath(),
			'name' => $name
		];
	}

	public function onException(Exception $err, ?RouteContext $context = null): void
	{
		pre_die(__METHOD__, func_get_args());
	}

	public function onNotFound(string $requestPath, RouteContext $context): bool
	{
		pre(__METHOD__, 'Route was not found, not found handled in MySubRouter.', $requestPath, $context->getResponseImplementation());

		return true;
	}

}

try
{
	$router = new MyRouter();
	$router->define('myBool', true);
	$router->define('request', new Request());
//	$router->execute('/profile/1/invoices/20191001.pdf', 'GET');
	$router->execute('/sub/bas', 'GET');
}
catch (RouterException $err)
{
	print_r($err);
}

echo PHP_EOL;
