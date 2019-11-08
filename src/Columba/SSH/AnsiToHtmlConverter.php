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

namespace Columba\SSH;

use const ENT_QUOTES;
use const ENT_SUBSTITUTE;
use const PHP_VERSION_ID;
use const PREG_OFFSET_CAPTURE;
use function explode;
use function htmlspecialchars;
use function in_array;
use function preg_match_all;
use function preg_replace;
use function sprintf;
use function strlen;
use function substr;

/**
 * Class AnsiToHtmlConverter
 *
 * @package Columba\SSH
 * @author Bas Milius <bas@mili.us>
 * @since 1.3.0
 */
final class AnsiToHtmlConverter
{

	public const COLORS = [
		'black' => '#191a1c',
		'red' => '#FF6B68',
		'green' => '#A8C023',
		'yellow' => '#D6BF55',
		'blue' => '#5394EC',
		'magenta' => '#AE8ABE',
		'cyan' => '#299999',
		'white' => '#FFFFFF',

		'brblack' => '#191a1c',
		'brred' => '#FF8785',
		'brgreen' => '#A8C023',
		'bryellow' => '#FFFF00',
		'brblue' => '#7EAEF1',
		'brmagenta' => '#FF99FF',
		'brcyan' => '#6CDADA',
		'brwhite' => '#999999',
	];

	public const COLOR_NAMES = [
		'black', 'red', 'green', 'yellow', 'blue', 'magenta', 'cyan', 'white',
		'', '',
		'brblack', 'brred', 'brgreen', 'bryellow', 'brblue', 'brmagenta', 'brcyan', 'brwhite',
	];

	/**
	 * Converts {@see $str} to HTML with ANSI colors.
	 *
	 * @param string $str
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function convert(string $str): string
	{
		$str = preg_replace('#\e\[(K|s|u|2J|2K|\d+(A|B|C|D|E|F|G|J|K|S|T)|\d+;\d+(H|f))#', '', $str);
		$str = preg_replace('#\e(\(|\))(A|B|[0-2])#', '', $str);
		$str = htmlspecialchars($str, PHP_VERSION_ID >= 50400 ? ENT_QUOTES | ENT_SUBSTITUTE : ENT_QUOTES, 'UTF-8');
		$str = preg_replace('#^.*\r(?!\n)#m', '', $str);

		$tokens = $this->tokenize($str);

		foreach ($tokens as $i => $token)
		{
			if ($token[0] == 'backspace')
			{
				$j = $i;

				while (--$j >= 0)
				{
					if ('text' == $tokens[$j][0] && strlen($tokens[$j][1]) > 0)
					{
						$tokens[$j][1] = substr($tokens[$j][1], 0, -1);
						break;
					}
				}
			}
		}

		$html = '';

		foreach ($tokens as $token)
			if ($token[0] == 'text')
				$html .= $token[1];
			else if ($token[0] == 'color')
				$html .= $this->convertAnsiToColor($token[1]);

		$html = sprintf('<span style="background-color: %s; color: %s">%s</span>', self::COLORS['black'], self::COLORS['white'], $html);
		$html = preg_replace('#<span[^>]*></span>#', '', $html);

		return $html;
	}

	/**
	 * Converts ANSI to Color.
	 *
	 * @param string $ansi
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	private function convertAnsiToColor(string $ansi): string
	{
		$bg = 0;
		$fg = 7;
		$as = '';

		if ($ansi != '0' && $ansi != '')
		{
			$options = explode(';', $ansi);

			foreach ($options as $option)
				if ($option >= 30 && $option < 38)
					$fg = $option - 30;
				else if ($option >= 40 && $option < 48)
					$bg = $option - 40;
				else if (39 == $option)
					$fg = 7;
				else if (49 == $option)
					$bg = 0;

			if (in_array(1, $options))
			{
				$fg += 10;
				$bg += 10;
			}

			if (in_array(4, $options))
				$as = '; text-decoration: underline';

			if (in_array(7, $options))
			{
				$tmp = $fg;
				$fg = $bg;
				$bg = $tmp;
			}
		}

		return sprintf('</span><span style="background-color: %s; color: %s%s">', self::COLORS[self::COLOR_NAMES[$bg]], self::COLORS[self::COLOR_NAMES[$fg]], $as);
	}

	/**
	 * Tokenizes the {@see $str}.
	 *
	 * @param string $str
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	private function tokenize($str): array
	{
		$tokens = [];
		$offset = 0;

		preg_match_all("/(?:\e\[(.*?)m|(\x08))/", $str, $matches, PREG_OFFSET_CAPTURE);

		foreach ($matches[0] as $i => $match)
		{
			if ($match[1] - $offset > 0)
				$tokens[] = ['text', substr($str, $offset, $match[1] - $offset)];

			$tokens[] = [$match[0] == "\x08" ? 'backspace' : 'color', $matches[1][$i][0]];
			$offset = $match[1] + strlen($match[0]);
		}

		if ($offset < strlen($str))
			$tokens[] = ['text', substr($str, $offset)];

		return $tokens;
	}

}
