<?php
/**
 * Copyright (c) 2019 - 2020 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

use Columba\Foundation\Http\Request;
use Columba\Http\RequestMethod;
use Columba\Router\Context;
use Columba\Router\Renderer\DebugRenderer;
use Columba\Router\Response\JsonResponse;
use Columba\Router\Router;
use Columba\Router\RouterException;
use Columba\Router\SubRouter;
use function Columba\Util\dump;
use function Columba\Util\pre;

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
		$this->get('$postId/comments', function (Context $context, int $postId): string
		{
			return $this->render('', [$postId, $context]);
		});
		$this->delete('$postId', returnString('DELETE request on a post'));
		$this->head('$postId', returnString('HEAD request on a post'));
		$this->options('$postId', returnString('OPTIONS request on a post'));
		$this->patch('$postId', returnString('PATCH request on a post'));
		$this->put('$postId', returnString('PUT request on a post'));
		$this->redirect('latest', '/news/204');
		$this->match(['GET', 'POST'], 'weird', [$this, 'onWeird']);
	}

	protected final function onWeird(): string
	{
		return 'Weird stuff';
	}

}

$router = new Router(new JsonResponse(), new DebugRenderer());
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

$router->get('request-test', function (Context $context, Request $request): void
{
	dump(
		$request->ip(),
		$request->isSecure(),
		$request->method(),
		$request->pathName(),
		$request->uri(),
		$request->userAgent()
	);
	pre(
		$request->body(),
		$request->bodyJson(),
		$request->bodyMultiPart(),
		$request->cookies(),
		$request->files(),
		$request->headers(),
		$request->post(),
		$request->queryString(),
		$request->server()
	);
	pre($context);
});

try
{
	$router->define('request', new Request());
	$router->execute('/users/edit', RequestMethod::GET);
}
catch (RouterException $err)
{
	if ($err->getCode() === $err::ERR_NOT_FOUND)
		echo $err->getMessage();
	else
		throw $err;
}
