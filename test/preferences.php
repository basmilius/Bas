<?php
declare(strict_types=1);

use Columba\Preferences;

require_once __DIR__ . '/bootstrap-test.php';

header('Content-Type: text/plain');

$preferences = Preferences::loadFromJson('preferences.json');

echo json_encode($preferences, JSON_PRETTY_PRINT);
