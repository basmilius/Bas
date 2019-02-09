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

use Columba\Router\Response\HtmlResponse;
use Columba\Router\RouteContext;
use Columba\Router\Router;
use Columba\Router\RouterException;
use Columba\Router\SubRouter;
use Columba\Util\Stopwatch;

require_once __DIR__ . '/../bootstrap-test.php';

header('Content-Type: text/plain');

class MyRouter extends Router
{

	public function __construct()
	{
		parent::__construct(new HtmlResponse());

		$this->get('/', [$this, 'onGetIndex']);
		$this->get('/(profile|user)/$userId', [$this, 'onGetUser']);
		$this->get('/(profile|user)/$userId/invoices/$invoiceNo.(?<format>pdf|html)', [$this, 'onGetUserInvoice']);
		$this->get('/download/invoice.$format', [$this, 'onGetInvoice']);

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

	public final function onGetUserInvoice(string $invoiceNo, string $format, int $userId = 10): string
	{
		return sprintf("Show invoice '%s' as '%s' for user %d.", $invoiceNo, $format, $userId);
	}

}

Stopwatch::start('router');

try
{
	$router = new MyRouter();
	$router->executeAndRespond('/profile/invoices/20181122.pdf', 'GET');
}
catch (RouterException $err)
{
	print_r($err);
}

Stopwatch::stop('router', $time, Stopwatch::UNIT_SECONDS);

echo PHP_EOL;
echo PHP_EOL;
echo sprintf('Executed in %g seconds', $time);
