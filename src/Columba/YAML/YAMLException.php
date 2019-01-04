<?php
declare(strict_types=1);

namespace Columba\YAML;

use Exception;
use Throwable;

/**
 * Class YAMLException
 *
 * @author Bas Milius <bas@ideemedia.nl>
 * @package Columba\YAML
 * @since 1.4.0
 */
final class YAMLException extends Exception
{

	public const ERR_FILE_NOT_FOUND = 0xAFAA91;

	/**
	 * YAMLException constructor.
	 *
	 * @param string         $message
	 * @param int            $code
	 * @param Throwable|null $previous
	 *
	 * @author Bas Milius <bas@ideemedia.nl>
	 * @since 1.4.0
	 */
	public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

}
