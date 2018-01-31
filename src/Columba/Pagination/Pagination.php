<?php
/**
 * Copyright (c) 2018 - Bas Milius <bas@mili.us>.
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Pagination;

/**
 * Class Pagination
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Pagination
 * @since 1.0.0
 */
final class Pagination
{

	private $base;

	private $current;
	private $items;
	private $itemsPerPage;
	private $pages = 0;
	private $sizeEnd;
	private $sizeMid;

	private $available = true;
	private $data;
	private $isMade = false;

	/**
	 * Pagination constructor.
	 *
	 * @param string $base
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct (string $base)
	{
		$this->base = $base;
		$this->current = 0;
		$this->items = 0;
		$this->itemsPerPage = 10;
		$this->sizeEnd = 2;
		$this->sizeMid = 2;
	}

	/**
	 * Gets the pagination data.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function get (): array
	{
		if (!$this->isMade)
			return $this->make();

		return $this->data;
	}

	/**
	 * Generates the pagination.
	 *
	 * @return array
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function make (): array
	{
		if ($this->isMade)
			return $this->data;

		$this->data = [];

		$this->pages = (int)ceil($this->items / $this->itemsPerPage);
		$this->current = max(min($this->current, $this->pages), 1);
		$this->available = $this->pages > 1;

		if (!$this->available)
			return $this->data = [];

		$showDots = false;
		$showPages = [];

		if ($this->sizeEnd < 1)
			$this->sizeEnd = 1;

		if ($this->sizeMid < 1)
			$this->sizeMid = 1;

		for ($n = 1; $n <= $this->pages; $n++)
		{
			if ($this->current === $n)
			{
				$showDots = true;
				$showPages[] = $n;
			}
			else
			{
				$showAll = false;

				if ($showAll || ($n <= $this->sizeEnd || ($this->current && $n >= $this->current - $this->sizeMid && $n <= $this->current + $this->sizeMid) || $n > $this->pages - $this->sizeEnd))
				{
					$showDots = true;
					$showPages[] = $n;
				}
				else if ($showDots && !$showAll)
				{
					$showDots = false;
					$showPages[] = -1;
				}
			}
		}

		if ($this->current > 1)
		{
			$this->data[] = new PaginationItem(1, false, false, false, true, 'first');
			$this->data[] = new PaginationItem($this->current - 1, false, false, false, true, 'prev');
		}

		foreach ($showPages as $page)
			$this->data[] = new PaginationItem($page, $page === $this->current, $page === -1, $page === -1, false, (string)$page);

		if ($this->current < $this->pages)
		{
			$this->data[] = new PaginationItem($this->current + 1, false, false, false, true, 'next');
			$this->data[] = new PaginationItem($this->pages, false, false, false, true, 'last');
		}

		$this->isMade = true;

		return $this->data;
	}

	/**
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function isAvailable (): bool
	{
		return $this->available;
	}

	/**
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getBase (): string
	{
		return $this->base;
	}

	/**
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getCurrent (): int
	{
		return $this->current;
	}

	/**
	 * @param int $current
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function setCurrent (int $current)
	{
		$this->current = $current;
	}

	/**
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getItems (): int
	{
		return $this->items;
	}

	/**
	 * @param int $items
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function setItems (int $items)
	{
		$this->items = $items;
	}

	/**
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getItemsPerPage (): int
	{
		return $this->itemsPerPage;
	}

	/**
	 * @param int $itemsPerPage
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function setItemsPerPage (int $itemsPerPage)
	{
		$this->itemsPerPage = $itemsPerPage;
	}

	/**
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getPages (): int
	{
		return $this->pages;
	}

	/**
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getSizeEnd (): int
	{
		return $this->sizeEnd;
	}

	/**
	 * @param int $sizeEnd
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function setSizeEnd (int $sizeEnd)
	{
		$this->sizeEnd = $sizeEnd;
	}

	/**
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getSizeMid (): int
	{
		return $this->sizeMid;
	}

	/**
	 * @param int $sizeMid
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function setSizeMid (int $sizeMid)
	{
		$this->sizeMid = $sizeMid;
	}

}
