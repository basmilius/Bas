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

use Columba\Security\JWT\JWT;
use function Columba\Util\pre;

require __DIR__ . '/../bootstrap-test.php';

header('Content-Type: text/plain');

pre(
	$jwt = JWT::encode(['lorem' => 'ipsum', 'exp' => time() + 1], 'bas'),
	JWT::decode($jwt, ['bas'])
);
