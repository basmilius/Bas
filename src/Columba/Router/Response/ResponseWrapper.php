<?php
declare(strict_types=1);

namespace Columba\Router\Response;

/**
 * Class ResponseWrapper
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Router\Response
 * @since 1.3.0
 */
final class ResponseWrapper
{

	/**
	 * @var AbstractResponse
	 */
	private $response;

	/**
	 * @var mixed
	 */
	private $value;

	/**
	 * ResponseWrapper constructor.
	 *
	 * @param AbstractResponse $response
	 * @param mixed            $value
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public function __construct(AbstractResponse $response, $value)
	{
		$this->response = $response;
		$this->value = $value;
	}

	/**
	 * Gets the response.
	 *
	 * @return AbstractResponse
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getResponse(): AbstractResponse
	{
		return $this->response;
	}

	/**
	 * Gets the value.
	 *
	 * @return mixed
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.3.0
	 */
	public final function getValue()
	{
		return $this->value;
	}

}
