<?php
declare(strict_types=1);

namespace Columba\Router\Response;

/**
 * Class SerializeResponse
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router\Response
 * @since 1.3.0
 */
final class SerializeResponse extends AbstractResponse
{

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	protected final function respond($value): string
	{
		$this->addHeader('Content-Type', 'text/plain');

		return serialize($value);
	}

}
