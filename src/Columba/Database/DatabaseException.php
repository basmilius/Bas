<?php
/**
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Database;

use Columba\Database\Lexer\Lexer;
use PDOException;

/**
 * Class DatabaseException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database
 * @since 1.0.0
 */
final class DatabaseException extends \Exception
{

	public const ERR_CLASS_NOT_FOUND = 0xDBA0019;
	public const ERR_FIELD_NOT_FOUND = 0xDBA0021;
	public const ERR_QUERY_FAILED = 0xDBA04039;

	/**
	 * DatabaseException constructor.
	 *
	 * @param string            $message
	 * @param int               $code
	 * @param PDOException|null $previous
	 * @param string|null       $query
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct (string $message, int $code, ?PDOException $previous = null, ?string $query = null)
	{
		parent::__construct($message, $code, $previous);

		if ($previous !== null && $query !== null)
		{
			$this->handleQuerySyntaxError($previous, $query);
		}
	}

	/**
	 * If we're in a query syntax exception, enhance the message output.
	 *
	 * @param PDOException $err
	 * @param string       $query
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	private function handleQuerySyntaxError (PDOException $err, string $query): void
	{
		try
		{
			$lexer = new Lexer($query);
			$lexer->setDatabase('admin_intranet');
			$lexer->setException($err);
			$lexer->lex();
			$tokens = $lexer->getTokens();

			$this->message = $tokens->getHtml();
		}
		catch (\ReflectionException $err)
		{
			$this->message = $err->getMessage();
		}
	}

}
