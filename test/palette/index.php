<?php
declare(strict_types=1);

use Bas\Util\ColorUtil;

require_once __DIR__ . '/../../src/Bas/Util/ColorUtil.php';
require_once __DIR__ . '/../../src/Bas/Util/MathUtil.php';

header('Content-Type: text/plain; charset=UTF-8');

print_r([
	$rgb = [204, 31, 75],
	$hsl = ColorUtil::rgbToHsl(...$rgb),
	ColorUtil::hslToRgb(...$hsl),
	ColorUtil::luminance(...$rgb),
	ColorUtil::blend([0, 0, 0], $rgb, 10)
]);
