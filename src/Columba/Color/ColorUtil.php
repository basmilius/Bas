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

namespace Columba\Color;

use Columba\Util\MathUtil;
use InvalidArgumentException;

/**
 * Class ColorUtil
 *
 * @package Columba\Color
 * @author Bas Milius <bas@mili.us>
 * @since 1.1.0
 */
final class ColorUtil
{

	/**
	 * Blends {@see $color1} with {@see $color2} with {@see $weight}.
	 *
	 * @param array $color1
	 * @param array $color2
	 * @param int   $weight
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function blend(array $color1, array $color2, int $weight = 0): array
	{
		$weight = MathUtil::clamp($weight, 0, 100);

		$percentage = $weight / 100;
		$scaledWeight = $percentage * 2 - 1;
		$alphaDiff = ($color1[3] ?? 1) - ($color2[3] ?? 1);

		$weight1 = (($scaledWeight * $alphaDiff === -1 ? $scaledWeight : ($scaledWeight + $alphaDiff) / (1 + $scaledWeight * $alphaDiff)) + 1) / 2;
		$weight2 = 1 - $weight1;

		$rgba = [
			intval(round($color1[0] * $weight1 + $color2[0] * $weight2)),
			intval(round($color1[1] * $weight1 + $color2[1] * $weight2)),
			intval(round($color1[2] * $weight1 + $color2[2] * $weight2)),
			$color1[3] ?? 1
		];

		return $rgba;
	}

	/**
	 * Returns a shade of {@see $color} with {@see $weight}.
	 *
	 * @param array $color
	 * @param int   $weight
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function shade(array $color, int $weight = 0): array
	{
		return self::blend([0, 0, 0], $color, $weight);
	}

	/**
	 * Returns a tint of {@see $color} with {@see $weight}.
	 *
	 * @param array $color
	 * @param int   $weight
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function tint(array $color, int $weight = 0): array
	{
		return self::blend([255, 255, 255], $color, $weight);
	}

	/**
	 * Gets the luminance of a RGB value.
	 *
	 * @param int $r
	 * @param int $g
	 * @param int $b
	 *
	 * @return float
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function luminance(int $r, int $g, int $b): float
	{
		$rgb = [$r, $g, $b];

		array_walk($rgb, function (int &$value): void
		{
			$value = $value / 255;

			if ($value < 0.03928)
				$value = $value / 12.92;
			else
				$value = pow(($value + .055) / 1.055, 2.4);
		});

		[$r, $g, $b] = $rgb;

		return ($r * .2126) + ($g * .7152) + ($b * .0722);
	}

	/**
	 * Calculates the YIQ of RGB.
	 *
	 * @param int $r
	 * @param int $g
	 * @param int $b
	 *
	 * @return float
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function yiq(int $r, int $g, int $b): float
	{
		return (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
	}

	/**
	 * Returns {@see $dark} if {@see $color} is a light color, otherwise it returns {@see $light}.
	 *
	 * @param array $color
	 * @param array $dark
	 * @param array $light
	 * @param float $delta
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function lightOrDark(array $color, array $dark = [0, 0, 0], array $light = [255, 255, 255], float $delta = 0.5): array
	{
		if (self::luminance(...$color) < $delta)
			return $light;

		return $dark;
	}

	/**
	 * Converts HEX to RGB.
	 *
	 * @param string $hex
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function hexToRgb(string $hex): array
	{
		[$r, $g, $b] = self::hexToRgba($hex);

		return [$r, $g, $b];
	}

	/**
	 * Converts HEX to RGBA.
	 *
	 * @param string $hex
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function hexToRgba(string $hex): array
	{
		$hex = trim($hex);

		$isHashtagHex = substr($hex, 0, 1) === '#';
		$hex = $isHashtagHex ? substr($hex, 1) : $hex;

		if (strlen($hex) === 8) // RRGGBBAA
		{
			preg_match('#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})#i', $hex, $matches);

			array_shift($matches);
			$matches = array_map('hexdec', $matches);

			[$r, $g, $b, $a] = $matches;

			return [$r, $g, $b, $a / 255];
		}

		if (strlen($hex) === 6)
		{
			preg_match('#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})#i', $hex, $matches);

			array_shift($matches);
			$matches = array_map('hexdec', $matches);

			[$r, $g, $b] = $matches;

			return [$r, $g, $b, 1];
		}

		if (strlen($hex) === 3)
		{
			preg_match('#([0-9a-f])([0-9a-f])([0-9a-f])#i', $hex, $matches);

			array_shift($matches);
			$matches = array_map('hexdec', $matches);

			[$r, $g, $b] = $matches;

			return [($r / 0xF) * 255, ($g / 0xF) * 255, ($b / 0xF) * 255, 1];
		}

		throw new InvalidArgumentException("Could not parse hex {$hex} to rgba.");
	}

	/**
	 * Converts a HSL color to RGB.
	 *
	 * @param float $h
	 * @param float $s
	 * @param float $l
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function hslToRgb(float $h, float $s, float $l): array
	{
		if ($s === 0)
			return [$l, $l, $l]; // Color is grey, only lightness is relevant.

		$chroma = (1 - abs(2 * $l - 1)) * $s;
		$h *= 6;
		$x = $chroma * (1 - abs((fmod($h, 2)) - 1));
		$m = $l - round($chroma / 2, 10);

		$r = 0;
		$g = 0;
		$b = 0;

		if ($h >= 0 && $h < 1)
		{
			$r = $chroma + $m;
			$g = $x + $m;
			$b = $m;
		}
		else if ($h >= 1 && $h < 2)
		{
			$r = $x + $m;
			$g = $chroma + $m;
			$b = $m;
		}
		else if ($h >= 2 && $h < 3)
		{
			$r = $m;
			$g = $chroma + $m;
			$b = $x + $m;
		}
		else if ($h >= 3 && $h < 4)
		{
			$r = $m;
			$g = $x + $m;
			$b = $chroma + $m;
		}
		else if ($h >= 4 && $h < 5)
		{
			$r = $x + $m;
			$g = $m;
			$b = $chroma + $m;
		}
		else if ($h >= 5 && $h < 6)
		{
			$r = $chroma + $m;
			$g = $m;
			$b = $x + $m;
		}

		return [
			round($r * 255),
			round($g * 255),
			round($b * 255)
		];
	}

	/**
	 * Converts an int color to RGB.
	 *
	 * @param int $color
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function intToRgb(int $color): array
	{
		$r = $color >> 16 & 0xFF;
		$g = $color >> 8 & 0xFF;
		$b = $color >> 0 & 0xFF;

		return [$r, $g, $b];
	}

	/**
	 * Converts an int color to RGBA.
	 *
	 * @param int $color
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function intToRgba(int $color): array
	{
		$a = $color >> 24 & 0xFF;
		$r = $color >> 16 & 0xFF;
		$g = $color >> 8 & 0xFF;
		$b = $color >> 0 & 0xFF;

		return [$r, $g, $b, $a];
	}

	/**
	 * Converts RGBA to HEX.
	 *
	 * @param int   $r
	 * @param int   $g
	 * @param int   $b
	 * @param float $a
	 * @param bool  $includeHashtag
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function rgbaToHex(int $r, int $g, int $b, float $a, bool $includeHashtag = false): string
	{
		return ($includeHashtag ? '#' : '') . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . str_pad(dechex($b), 2, '0', STR_PAD_LEFT) . str_pad(dechex($a * 255), 2, '0', STR_PAD_LEFT);
	}

	/**
	 * Converts RGB to HEX.
	 *
	 * @param int  $r
	 * @param int  $g
	 * @param int  $b
	 * @param bool $includeHashtag
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function rgbToHex(int $r, int $g, int $b, bool $includeHashtag = false): string
	{
		return ($includeHashtag ? '#' : '') . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
	}

	/**
	 * Converts a RGB value to int.
	 *
	 * @param int $r
	 * @param int $g
	 * @param int $b
	 *
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function rgbToInt(int $r, int $g, int $b): int
	{
		return ($r << 16) | ($g << 8) | ($b << 0);
	}

	/**
	 * Converts a RGB value to HSL.
	 *
	 * @param int $r
	 * @param int $g
	 * @param int $b
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.1.0
	 */
	public static function rgbToHsl(int $r, int $g, int $b): array
	{
		$r /= 255;
		$g /= 255;
		$b /= 255;

		$max = max($r, $g, $b);
		$min = min($r, $g, $b);
		$chroma = $max - $min;

		$l = ($max + $min) / 2;

		if ($max === $min)
		{
			// Achromatic
			$h = $s = 0.0;
		}
		else
		{
			$h = 0;

			if ($max === $r)
			{
				$h = fmod((($g - $b) / $chroma), 6);

				if ($h < 0)
					$h = (6 - fmod(abs($h), 6));
			}
			else if ($max === $g)
			{
				$h = ($b - $r) / $chroma + 2;
			}
			else if ($max === $b)
			{
				$h = ($r - $g) / $chroma + 4;
			}

			$h /= 6;
			$s = $chroma / (1 - abs(2 * $l - 1));
		}

		return [
			round($h, 3),
			round($s, 3),
			round($l, 3)
		];
	}

}
