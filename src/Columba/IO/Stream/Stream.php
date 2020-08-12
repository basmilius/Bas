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

use function array_merge;
use function array_values;
use function call_user_func_array;
use function chr;
use function count;
use function fclose;
use function fopen;
use function fread;
use function fseek;
use function fwrite;
use function ord;
use function stream_copy_to_stream;
use function stream_get_contents;
use function unpack;

/**
 * Class Stream
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\IO\Stream
 * @since 1.6.0
 */
abstract class Stream
{

	/** @var resource */
	protected $handle;

	/**
	 * Stream constructor.
	 *
	 * @param resource $handle
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct($handle)
	{
		$this->handle = $handle;
	}

	/**
	 * Reads the given amount of bytes and puts it into the given array buffer.
	 *
	 * @param int $bytesCount
	 * @param array $buffer
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function readBytes(int $bytesCount, array &$buffer): void
	{
		if ($bytesCount === 1)
		{
			$buffer = [ord(fread($this->handle, 1))];
		}
		else
		{
			$bytes = fread($this->handle, $bytesCount);
			$buffer = array_values(unpack('C*', $bytes));
		}
	}

	/**
	 * Writes the given bytes.
	 *
	 * @param array $bytes
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function writeBytes(array $bytes): void
	{
		if (count($bytes) === 1)
			fwrite($this->handle, chr($bytes[0]));
		else
			fwrite($this->handle, call_user_func_array('pack', array_merge(['C*'], $bytes)));
	}

	/**
	 * Writes the given string.
	 *
	 * @param string $text
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function writeString(string $text): void
	{
		fwrite($this->handle, $text);
	}

	/**
	 * Gets all contents of the stream.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getContents(): string
	{
		$this->seek(0);

		return stream_get_contents($this->handle);
	}

	/**
	 * Closes the stream.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function close(): void
	{
		fclose($this->handle);
	}

	/**
	 * Copies all contents to the given file.
	 *
	 * @param string $fileName
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function copyContentsToFile(string $fileName): void
	{
		$fp = fopen($fileName, 'wb');
		$this->seek(0);

		stream_copy_to_stream($this->handle, $fp);
		fclose($fp);
	}

	/**
	 * Gets the stream handle.
	 *
	 * @return resource
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function getHandle()
	{
		return $this->handle;
	}

	/**
	 * Moves the cursor.
	 *
	 * @param int $offset
	 * @param int $whence
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function seek(int $offset, int $whence = SEEK_SET): void
	{
		fseek($this->handle, $offset, $whence);
	}

	/**
	 * Returns TRUE if the stream has reached its end.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function hasReachedEOF(): bool
	{
		return feof($this->handle);
	}

}
