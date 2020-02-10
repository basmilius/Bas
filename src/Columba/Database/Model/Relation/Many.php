<?php
/**
 * Copyright (c) 2019 - 2020 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Columba package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Database\Model\Relation;

use Columba\Data\Collection;
use Columba\Database\Model\Model;
use Columba\Database\Query\Builder\Builder;

/**
 * Class Many
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Model\Relation
 * @since 1.6.0
 */
class Many extends Relation
{

	private string $referenceKey;
	private string $selfKey;

	/**
	 * Many constructor.
	 *
	 * @param Model|string $referenceModel
	 * @param string|null  $referenceKey
	 * @param string|null  $selfKey
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(string $referenceModel, ?string $referenceKey = null, ?string $selfKey = null)
	{
		parent::__construct($referenceModel);

		$this->referenceKey = $referenceKey ?? $referenceModel::table() . '_id';
		$this->selfKey = $selfKey ?? 'id';
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function get(): Collection
	{
		return $this->collection();
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function relevantColumns(): array
	{
		return [];
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function buildBaseQuery(): Builder
	{
		return $this->where($this->referenceKey, $this->model[$this->selfKey] ?? 0);
	}

}
