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

use Columba\Image\Image;
use Columba\Palette\Palette;

require_once __DIR__ . '/../../src/Columba/Color/Color.php';
require_once __DIR__ . '/../../src/Columba/Color/ColorUtil.php';
require_once __DIR__ . '/../../src/Columba/Image/Image.php';
require_once __DIR__ . '/../../src/Columba/Palette/ColorCutQuantizer.php';
require_once __DIR__ . '/../../src/Columba/Palette/ColorHistogram.php';
require_once __DIR__ . '/../../src/Columba/Palette/Palette.php';
require_once __DIR__ . '/../../src/Columba/Palette/Swatch.php';
require_once __DIR__ . '/../../src/Columba/Palette/Vbox.php';
require_once __DIR__ . '/../../src/Columba/Util/MathUtil.php';

//header('Content-Type: text/plain; charset=UTF-8');

$image = '8.jpg';

try
{
	$start = microtime(true);
	$palette = Palette::generate(Image::fromFile(__DIR__ . '/photos/' . $image));
	$total = microtime(true) - $start;

	echo '<!DOCTYPE html>
<html>
<head>
	<title>Palette</title>
</head>
<body>
	<img height="240" src="./photos/' . $image . '" />
	
	<table>
		<thead>
		<tr>
			<th>Type</th>
			<th>Color</th>
		</tr>
		</thead>
		<tbody>';

	foreach ($palette->getDefinedSwatches() as $name => $swatch)
	{
		if ($swatch === null)
			continue;

		echo '<tr><td>' . $name . '</td><td><div style="position:relative;display:block;height:36px;width:36px;background:rgb(' . implode(',', $swatch->getColor()->getRgb()) . ')"></div></td></tr>';
	}

	echo '</tbody></table>';

	echo '<br/><br/><br/>Generated in ' . number_format($total, 6, '.', ',') . ' seconds.';

	echo '
	</body>
</html>';

}
catch (Exception $err)
{
	print_r($err);
}
