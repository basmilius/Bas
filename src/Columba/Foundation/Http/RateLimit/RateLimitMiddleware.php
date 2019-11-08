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

namespace Columba\Foundation\Http\RateLimit;

use Columba\Http\ResponseCode;
use Columba\Router\Context;
use Columba\Router\Middleware\AbstractMiddleware;
use Columba\Router\Response\JsonResponse;
use Columba\Router\Route\AbstractRoute;
use Columba\Router\Router;
use function strval;

/**
 * Class RateLimitMiddleware
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Foundation\Http\RateLimit
 * @since 1.6.0
 */
class RateLimitMiddleware extends AbstractMiddleware
{

	/**
	 * @var RateLimit
	 */
	private $rateLimit;

	/**
	 * RateLimitMiddleware constructor.
	 *
	 * @param Router    $router
	 * @param RateLimit $rateLimit
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(Router $router, RateLimit $rateLimit)
	{
		parent::__construct($router);

		$this->rateLimit = $rateLimit;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function forContext(AbstractRoute $route, Context $context, bool &$isValid): void
	{
		if (!$isValid)
			return;

		$id = $_SERVER['REMOTE_ADDR'];
		$isValid = $this->rateLimit->isAllowed($id);

		$context->addParam('rateLimitStatus', [
			'allowance' => $this->rateLimit->getAllowance($id),
			'name' => $this->rateLimit->getName(),
			'period' => $this->rateLimit->getPeriod(),
			'requests' => $this->rateLimit->getRequests()
		]);

		if ($isValid)
			return;

		$isValid = true;

		$this->makeRateLimitExceededResponse($context, new RateLimitExceededException());
	}

	/**
	 * Makes the response clients get when the rate limit is exceeded.
	 *
	 * @param Context                    $context
	 * @param RateLimitExceededException $err
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function makeRateLimitExceededResponse(Context $context, RateLimitExceededException $err): void
	{
		$response = new JsonResponse(false);
		$response->addHeader('Retry-After', strval($this->rateLimit->getPeriod()));

		$context->setResponseCode(ResponseCode::TOO_MANY_REQUESTS);
		$context->setResponse($response, $err);
	}

}
