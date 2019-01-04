<?php
declare(strict_types=1);

use Columba\YAML\YAML;

require __DIR__ . '/../bootstrap-test.php';

$yaml = YAML::fromFile(__DIR__ . '/config.yaml');

pre_die($yaml);
