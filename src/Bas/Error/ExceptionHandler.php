<?php
/**
 * This file is part of the Bas package.
 *
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bas\Error;

use Bas\Cappuccino\Cappuccino;
use Bas\Cappuccino\Loader\ArrayLoader;
use Bas\Util\ExceptionUtil;
use Exception;
use Throwable;

/**
 * Class ExceptionHandler
 *
 * @author Bas Milius <bas@mili.us>
 * @package Bas\Error
 * @since 1.0.0
 */
final class ExceptionHandler
{

	/**
	 * Handles the exception.
	 *
	 * @param Throwable $err
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 * @internal
	 */
	public final function onException (Throwable $err): void
	{
		if (!$this->isCappuccinoAvailable())
			$this->handleWithoutCappuccino($err);
		else
			$this->handleWithCappuccino($err);
	}

	/**
	 * Handles the exception with Cappuccino.
	 *
	 * @param Throwable $err
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function handleWithCappuccino (Throwable $err): void
	{
		try
		{
			$cappuccino = new Cappuccino(new ArrayLoader());
			$template = $cappuccino->createTemplate($this->getCappuccinoTemplate());

			$exceptions = ExceptionUtil::exceptionToExceptions($err);

			print_r($exceptions);

			die($template->render([]));
		}
		catch (Exception $weFailed)
		{
			die($weFailed->getMessage());
		}
	}

	/**
	 * Handles the exception without Cappuccino.
	 *
	 * @param Throwable $err
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function handleWithoutCappuccino (Throwable $err): void
	{
		header('Content-Type: text-plain');
		echo get_class($err) . ' (' . $err->getCode() . '): ' . $err->getMessage();

		// TODO(Bas): Enhance this? Maybe?
	}

	/**
	 * Gets our basic Cappuccino template.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function getCappuccinoTemplate (): string
	{
		return <<<CAPPUCCINO
<!DOCTYPE html>
<html>
<head>
	<title>Error!</title>
	<style type="text/css">
	</style>
</head>
<body>
	Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ab aperiam aspernatur aut beatae commodi consectetur consequatur, cumque deserunt et expedita fugit harum minus natus necessitatibus perferendis quam quidem repellendus, reprehenderit.
</body>
</html>
CAPPUCCINO;
	}

	/**
	 * Returns TRUE if Cappuccino is available.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function isCappuccinoAvailable (): bool
	{
		return class_exists('Bas\\Cappuccino\\Cappuccino');
	}

	/**
	 * Registers the exception handler.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function register (): void
	{
		set_exception_handler([new self(), 'onException']);
	}

}
