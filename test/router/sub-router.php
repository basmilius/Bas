<?php
declare(strict_types=1);

use Columba\Router\JsonResponse;
use Columba\Router\Router;

/**
 * Class SubRouter
 *
 * @author Bas Milius <bas@mili.us>
 * @since 1.4.0
 */
final class SubRouter extends Router
{

	/**
	 * SubRouter constructor.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function __construct ()
	{
		parent::__construct();
	}

}

$router = new Router(new JsonResponse());
$router->use('/sub', new SubRouter());
