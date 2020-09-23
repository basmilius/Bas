<?php
declare(strict_types=1);

namespace Columba\Database\Model\Event;

use Columba\Database\Model\Model;

/**
 * Class AbstractEventListener
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Model\Event
 * @since 1.6.0
 */
abstract class AbstractEventListener
{

	/**
	 * Called when a model record is deleted.
	 *
	 * @param Model $model
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function onDeleted(Model $model): void
	{
	}

	/**
	 * Called when a new model record is inserted.
	 *
	 * @param Model $model
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function onInserted(Model $model): void
	{
	}

	/**
	 * Called when a model record is updated.
	 *
	 * @param Model $model
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function onUpdated(Model $model): void
	{
	}

}
