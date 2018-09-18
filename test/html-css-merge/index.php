<?php
declare(strict_types=1);

use Columba\HtmlCssMerge\HtmlCssMerger;

require_once __DIR__ . '/../bootstrap-test.php';

$css = file_get_contents('https://latte.dev-preview.nl/resource/scss/app.css');
$html = file_get_contents('https://ervetank.nl');

$merger = new HtmlCssMerger($html, $css);
$merger->groupByMediaQueries();

