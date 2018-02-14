<?php
/**
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use Columba\Request\Validate\RequestValidator;
use Columba\Request\Validate\RequestValidatorOption;

require_once __DIR__ . '/../bootstrap-test.php';

$result = RequestValidator::use()
	->with(
		RequestValidatorOption::expect('must_be_string|is')->toBeString(),
		RequestValidatorOption::expect('must_be_string|is_not')->toBeString()
	)
	->toValidate([
		'must_be_string|is' => 'Lorem ipsum dolor sit amet',
		'must_be_string|is_not' => 39
	]);

print_r($result);
