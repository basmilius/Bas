<?php
/**
 * Copyright (c) 2019 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Event;

use function array_search;

/**
 * Trait EventEmitter
 *
 * @package Columba\Event
 * @author Bas Milius <bas@mili.us>
 * @since 1.0.0
 */
trait EventEmitter
{

	private array $listeners = [];

	/**
	 * Adds an event listener.
	 *
	 * @param string   $eventName
	 * @param callable $callback
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function addEventListener(string $eventName, callable $callback): void
	{
		$this->ensureEventExists($eventName);

		$this->listeners[$eventName][] = $callback;
	}

	/**
	 * Dispatches an {@see Event}.
	 *
	 * @param string $eventName
	 * @param Event  $event
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	protected final function dispatchEvent(string $eventName, Event $event): void
	{
		$this->ensureEventExists($eventName);

		foreach ($this->listeners[$eventName] as $listener)
			if (!$event->isCancelled())
				$event->call($listener);
	}

	/**
	 * Removes an event listener.
	 *
	 * @param string   $eventName
	 * @param callable $callback
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function removeEventListeners(string $eventName, callable $callback): void
	{
		$this->ensureEventExists($eventName);

		if (($callbackKey = array_search($callback, $this->listeners[$eventName])))
			unset($this->listeners[$eventName][$callbackKey]);
	}

	/**
	 * Ensures that an event exists.
	 *
	 * @param string $eventName
	 *
	 * @access private
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 * @internal
	 */
	private final function ensureEventExists(string $eventName): void
	{
		if (!isset($this->listeners[$eventName]))
			$this->listeners[$eventName] = [];
	}

}
