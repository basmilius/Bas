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
		parent::__construct(new HtmlResponse());

		$this->get('/', [$this, 'onGetIndex']);
		$this->get('/(profile|user)/$userId', [$this, 'onGetUser']);
		$this->get('/(profile|user)/$userId/invoices/$invoiceNo.(?P<format>pdf|html)', [$this, 'onGetUserInvoice']);
		$this->get('/download/invoice.$format', [$this, 'onGetInvoice']);

		$this->get('/anonymous', function (): void
		{
			echo 'Hello world!';
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

	public final function onGetUser(int $userId): string
	{
		return 'Route: /user/' . $userId;
	}

	public final function onGetUserInvoice(int $userId, string $invoiceNo, string $format): string
	{
		return sprintf("Show invoice '%s' as '%s' for user %d.", $invoiceNo, $format, $userId);
	}

}

try
{
	$router = new MyRouter();
	$router->executeAndRespond('/anonymous', 'GET');
}
catch (RouterException $err)
{
	print_r($err);
}
