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

namespace Columba\Router\Renderer;

use Columba\Util\ArrayUtil;
use function count;
use function print_r;

/**
 * Class DebugRenderer
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router\Renderer
 * @since 1.5.0
 */
class DebugRenderer extends AbstractRenderer
{

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since
	 */
	public function render(string $template, array $context = []): string
	{
		if (count($context) === 1 && ArrayUtil::isSequentialArray($context))
			$context = $context[0];

		return print_r($context, true);
	}

}
