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

namespace Columba\Util;

use function array_rand;
use function count;
use function floor;
use function sqrt;
use function str_shuffle;
use function str_split;
use function strlen;
use function substr;

/**
 * Class AuthUtil
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Util
 * @since 1.4.0
 */
final class AuthUtil
{

	/**
	 * Creates a random password.
	 *
	 * @param int $length
	 * @param bool $addDashes
	 * @param string $availableSets
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public static function randomPassword(int $length = 9, bool $addDashes = false, string $availableSets = 'luds'): string
	{
		$sets = [];

		if (str_contains($availableSets, 'l'))
			$sets[] = 'abcdefghjkmnpqrstuvwxyz';

		if (str_contains($availableSets, 'u'))
			$sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';

		if (str_contains($availableSets, 'd'))
			$sets[] = '123456789';

		if (str_contains($availableSets, 's'))
			$sets[] = '!@#$%&*?';

		$all = '';
		$password = '';

		foreach ($sets as $set)
		{
			$password .= $set[array_rand(str_split($set))];
			$all .= $set;
		}

		$all = str_split($all);

		for ($i = 0, $setsCount = count($sets); $i < $length - $setsCount; ++$i)
			$password .= $all[array_rand($all)];

		$password = str_shuffle($password);

		if (!$addDashes)
			return $password;

		$dashLength = floor(sqrt($length));
		$dashString = '';

		while (strlen($password) > $dashLength)
		{
			$dashString .= substr($password, 0, $dashLength) . '-';
			$password = substr($password, $dashLength);
		}

		return $dashString . $password;
	}

}
