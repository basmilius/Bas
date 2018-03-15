<?php
declare(strict_types=1);

use Columba\Router\Response\HtmlResponse;
use Columba\Router\Router;

require_once __DIR__ . '/../bootstrap-test.php';

class MyException extends Exception implements JsonSerializable
{

	public function __construct (string $message, int $code)
	{
		parent::__construct($message, $code);
	}

	public final function jsonSerialize (): array
	{
		return [
			'code' => $this->code,
			'message' => $this->message
		];
	}

}

class MyRouter extends Router
{

	public function __construct ()
	{
		parent::__construct(new HtmlResponse());

		$this->get('/', [$this, 'onGetIndex']);
	}

	public final function onGetIndex (): string
	{
		throw new MyException('Lol!', 10);

		return 'Hi!';
	}

	public final function init (): void
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->handle(str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['REQUEST_URI']));
	}

}

$router = new MyRouter();
$router->init();
