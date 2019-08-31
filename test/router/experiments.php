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

use Columba\Router\Response\JsonResponse;
use Columba\Router\Router;
use Columba\Router\RouterException;
use Columba\Router\SubRouter;

/** @noinspection PhpMultipleClassesDeclarationsInOneFile */

require_once __DIR__ . '/../bootstrap-test.php';

function returnString(string $str): callable
{
	return function () use ($str): string
	{
		return $str;
	};
}

class EXNewsRouter extends SubRouter
{

	public function __construct()
	{
		parent::__construct();

		$this->get('', returnString('Index on /news'));
		$this->get('$postId', function (int $postId): string
		{
			return sprintf('News post #%d on /news/$postId', $postId);
		});
	}

}

$router = new Router(new JsonResponse());
$router->get('', returnString('Index on /'));
$router->all('news', EXNewsRouter::class);
$router->group('users', function (Router $users): void
{
	$users->get('', returnString('List with users on /users'));
	$users->get('$userId', function (int $userId): string
	{
		return sprintf('User #%d on /users/$userId', $userId);
	});
	$users->group('edit', function (Router $edit): void
	{
		$edit->get('', returnString('Edit portal on /users/edit'));
		$edit->get('password', returnString('Edit password on /users/edit/password'));
	});
});

try
{
	$router->execute('/news/203', 'GET');
}
catch (RouterException $err)
{
	if ($err->getCode() === $err::ERR_NOT_FOUND)
		echo $err->getMessage();
	else
		throw $err;
}
