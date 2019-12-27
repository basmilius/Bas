<?php
/**
 * Copyright (c) 2017 - 2019 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Latte Framework package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Image\GIF;

use function fclose;
use function feof;
use function fopen;
use function fread;
use function preg_match_all;

/**
 * Class GIFUtil
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Image\GIF
 * @since 1.6.0
 */
final class GIFUtil
{

	/**
	 * Returns TRUE if the given file is an animated GIF.
	 *
	 * @param string $filename
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public static function isAnimatedGIF(string $filename): bool
	{
		if (!($fh = @fopen($filename, 'rb')))
			return false;

		$count = 0;

		while (!feof($fh) && $count < 2)
		{
			$chunk = fread($fh, 1024 * 100);
			$count += preg_match_all('#\x00\x21\xF9\x04.{4}\x00([,!])#s', $chunk, $matches);
		}

		fclose($fh);

		return $count > 1;
	}

}
