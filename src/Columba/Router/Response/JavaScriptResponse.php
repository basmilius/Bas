<?php
declare(strict_types=1);

namespace Columba\Router\Response;

/**
 * Class JavaScriptResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router\Response
 * @since 1.3.0
 */
final class JavaScriptResponse extends AbstractResponse
{

	/**
	 * JavaScriptResponse constructor.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct()
	{
		parent::__construct();

		$this->addHeader('Content-Type', 'text/javascript; charset=utf-8');
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected final function respond($value): string
	{
		return $value;
	}

}
