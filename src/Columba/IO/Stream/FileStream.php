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

namespace Columba\IO\Stream;

use function fopen;
use function stream_set_read_buffer;

/**
 * Class FileStream
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\IO\Stream
 * @since 1.6.0
 */
class FileStream extends Stream
{

	protected string $fileName;

	/**
	 * FileStream constructor.
	 *
	 * @param string $fileName
	 * @param string $mode
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(string $fileName, string $mode = 'rb')
	{
		parent::__construct(fopen($fileName, $mode));

		$this->fileName = $fileName;

		stream_set_read_buffer($this->handle, 1024 * 1024);
		$this->seek(0);
	}

}
