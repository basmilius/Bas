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

namespace Columba\Plesk;

/**
 * Class PleskSubSystem
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Plesk
 * @since 1.0.0
 */
abstract class PleskSubSystem
{

	/**
	 * @var PleskApiClient
	 */
	protected $client;

	/**
	 * PleskSubSystem constructor.
	 *
	 * @param PleskApiClient $client
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct(PleskApiClient $client)
	{
		$this->client = $client;
	}

}
