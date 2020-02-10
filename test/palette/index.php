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

use Columba\Image\Image;
use Columba\Palette\Palette;

require_once __DIR__ . '/../bootstrap-test.php';

try
{
	$start = microtime(true);
	$palette = Palette::generate(Image::fromFile(__DIR__ . '/photo.jpg'));
	$total = microtime(true) - $start;

	echo '<!DOCTYPE html>
<html lang="en">
<head>
	<title>Palette</title>
</head>
<body>
	<img style="height: 200px" src="./photo.jpg" alt="Example photo" />
	
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
