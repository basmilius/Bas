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

namespace Columba\Pagination;

use JsonSerializable;

/**
 * Class PaginationItem
 *
 * @package Columba\Pagination
 * @author Bas Milius <bas@mili.us>
 * @since 1.0.0
 */
final class PaginationItem implements JsonSerializable
{

	/**
	 * @var int
	 */
	private $page;

	/**
	 * @var bool
	 */
	private $isCurrent;

	/**
	 * @var bool
	 */
	private $isDisabled;

	/**
	 * @var bool
	 */
	private $isDots;

	/**
	 * @var bool
	 */
	private $isNav;

	/**
	 * @var string
	 */
	private $label;

	/**
	 * PaginationItem constructor.
	 *
	 * @param int    $page
	 * @param bool   $isCurrent
	 * @param bool   $isDisabled
	 * @param bool   $isDots
	 * @param bool   $isNav
	 * @param string $label
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function __construct(int $page, bool $isCurrent, bool $isDisabled, bool $isDots, bool $isNav, string $label)
	{
		$this->page = $page;
		$this->isCurrent = $isCurrent;
		$this->isDisabled = $isDisabled;
		$this->isDots = $isDots;
		$this->isNav = $isNav;
		$this->label = $label;
	}

	/**
	 * @return int
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function getPage(): int
	{
		return $this->page;
	}

	/**
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function isCurrent(): bool
	{
		return $this->isCurrent;
	}

	/**
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function isDisabled(): bool
	{
		return $this->isDisabled;
	}

	/**
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function isDots(): bool
	{
		return $this->isDots;
	}

	/**
	 * @return bool
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function isNav(): bool
	{
		return $this->isNav;
	}

	/**
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getLabel(): string
	{
		if ($this->page === -1 && $this->label === '-1')
			return 'dots';

		return $this->label;
	}

	/**
	 * {@inheritdoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function jsonSerialize(): array
	{
		return [
			'page' => $this->page,
			'is_current' => $this->isCurrent,
			'is_disabled' => $this->isDisabled,
			'is_dots' => $this->isDots,
			'is_nav' => $this->isNav,
			'label' => $this->label
		];
	}

}
