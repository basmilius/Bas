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

namespace Columba\Plesk;

/**
 * Class PleskSubSystem
 *
 * @package Columba\Plesk
 * @author Bas Milius <bas@mili.us>
 * @since 1.0.0
 */
abstract class PleskSubSystem
{

	protected PleskApiClient $client;

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
