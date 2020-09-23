<?php
declare(strict_types=1);

namespace Columba\Database\Model\Event;

use Columba\Database\Model\Model;

/**
 * Class CallablesEventListener
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Model\Event
 * @since 1.6.0
 */
final class CallablesEventListener extends AbstractEventListener
{

	private array $onDeletedCallables = [];
	private array $onInsertedCallables = [];
	private array $onUpdatedCallables = [];

	/**
	 * Adds a listener for the deleted event.
	 *
	 * @param callable $fn
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function addDeletedListener(callable $fn): void
	{
		$this->onDeletedCallables[] = $fn;
	}

	/**
	 * Adds a listener for the inserted event.
	 *
	 * @param callable $fn
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function addInsertedListener(callable $fn): void
	{
		$this->onInsertedCallables[] = $fn;
	}

	/**
	 * Adds a listener for the updated event.
	 *
	 * @param callable $fn
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function addUpdatedListener(callable $fn): void
	{
		$this->onUpdatedCallables[] = $fn;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function onDeleted(Model $model): void
	{
		foreach ($this->onDeletedCallables as $fn)
			$fn($model);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function onInserted(Model $model): void
	{
		foreach ($this->onInsertedCallables as $fn)
			$fn($model);
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function onUpdated(Model $model): void
	{
		foreach ($this->onUpdatedCallables as $fn)
			$fn($model);
	}

}
