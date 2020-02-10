<?php
/**
 * Copyright (c) 2019 - 2020 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

/** @noinspection PhpMultipleClassesDeclarationsInOneFile */

declare(strict_types=1);

use Columba\Contract\Contract;
use function Columba\Util\pre;

require __DIR__ . '/../bootstrap-test.php';

header('Content-Type: text/plain');

$contract = new Contract();
$contract->term('firstname')->string();
$contract->term('lastname')->string();
$contract->term('age')->optional()->numeric()->between(20, 30);
$contract->term('email')->string()->email();

pre(
	$data = [
		'firstname' => 'Bas',
		'lastname' => 'Milius',
		'age' => '23',
		'email' => 'bas@mili.us',
		'non_existent' => 'Hello world!'
	],
	$contract->met($data),
	$data,
	$contract
);
