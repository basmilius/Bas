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

use Columba\Util\StringUtil;
use function Columba\Util\pre;

require_once __DIR__ . '/../bootstrap-test.php';

header('Content-Type: text/plain');

$sentences = <<<SENTENCES
I currently have 4 windows open up... and I don’t know why.
Wednesday is hump day, but has anyone asked the camel if he’s happy about it?
I checked to make sure that he was still alive.
What was the person thinking when they discovered cow’s milk was fine for human consumption… and why did they do it in the first place!?
She advised him to come back at once.
SENTENCES;

pre(
	StringUtil::splitSentences($sentences),
	StringUtil::endsWith('Hello world', 'world'),
	StringUtil::startsWith('Hello world', 'Hello'),
	StringUtil::commaCommaAnd('A', 'B', 'C'),
	StringUtil::slugify('My "amazing" title that is longer than 10 characters.'),
	StringUtil::toPascalCase('router exception class'),
	StringUtil::toSnakeCase('RouterException'),
	StringUtil::truncateText($sentences, 10, '[...]')
);
