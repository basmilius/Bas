<?php
declare(strict_types=1);

namespace Columba\Facade;

use JsonSerializable;

/**
 * Interface IJson
 *
 * @package Columba\Facade
 * @author Bas Milius <bas@mili.us>
 * @since 1.4.0
 */
interface IJson extends JsonSerializable
{

	/**
	 * Returns which data should be available in json.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.4.0
	 */
	public function jsonSerialize(): array;

}
