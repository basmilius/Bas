<?php
/**
 * Copyright © 2018 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Latte Framework package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\OAuth2\Router;

use Columba\OAuth2\OAuth2;
use Columba\Router\Renderer\AbstractRenderer;
use Columba\Router\Response\AbstractResponse;
use Columba\Router\Router;

/**
 * Class OAuth2AwareRouter
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\OAuth2\Router
 * @since 1.3.0
 */
abstract class AbstractOAuth2AwareRouter extends Router
{

	/**
	 * @var OAuth2
	 */
	protected $oAuth2;

	/**
	 * OAuth2AwareRouter constructor.
	 *
	 * @param OAuth2                $oAuth2
	 * @param AbstractResponse|null $response
	 * @param AbstractRenderer|null $renderer
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct (OAuth2 $oAuth2, ?AbstractResponse $response = null, ?AbstractRenderer $renderer = null)
	{
		parent::__construct($response, $renderer);

		$this->oAuth2 = $oAuth2;
	}

}
