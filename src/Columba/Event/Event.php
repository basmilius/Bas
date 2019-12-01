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

/**
 * Class Event
 *
 * @package Columba\Event
 * @author Bas Milius <bas@mili.us>
 * @since 1.0.0
 */
class Event
{

	private bool $cancelled;
	private array $data;

	/** @var mixed */
	private $target;

	/**
	 * Event constructor.
	 *
	 * @param mixed $target
	 * @param mixed $data
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct($target, ...$data)
	{
		$this->cancelled = false;
		$this->data = $data;
		$this->target = $target;
	}

	/**
	 * Calls an event listener.
	 *
	 * @param callable $listener
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function call(callable $listener): void
	{
		$listener($this, ...$this->data);
	}

	/**
	 * Cancels the event.
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function cancel(): void
	{
		$this->cancelled = true;
	}

	/**
	 * Returns TRUE if the event is cancelled.
	 *
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function isCancelled(): bool
	{
		return $this->cancelled;
	}

}
