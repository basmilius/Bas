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

use Columba\Collection\ArrayList;
use Columba\Database\Model\Model;
use Columba\Database\Query\Builder\Builder;

/**
 * Class ManyMany
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Model\Relation
 * @since 1.6.0
 */
class ManyMany extends Relation
{

	private string $linkingTable;
	private string $referenceKey;
	private string $selfKey;

	/**
	 * ManyMany constructor.
	 *
	 * @param Model|string $referenceModel
	 * @param string $linkingTable
	 * @param string|null $selfKey
	 * @param string|null $referenceKey
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(string $referenceModel, string $linkingTable, ?string $selfKey = null, ?string $referenceKey = null)
	{
		parent::__construct($referenceModel);

		$this->linkingTable = $linkingTable;
		$this->referenceKey = $referenceKey ?? $referenceModel::table() . '.id';
		$this->selfKey = $selfKey ?? $referenceModel::table() . '_id';
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function get(): ArrayList
	{
		return $this->arrayList();
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
		return $this
			->leftJoin($this->linkingTable, fn(Builder $query) => $query
				->on(Model::column($this->referenceKey, $this->linkingTable), '=', $this->referenceModel::column($this->referenceModel::primaryKey())))
			->where(Model::column($this->selfKey, $this->linkingTable), '=', $this->model[$this->model::primaryKey()]);
	}

}
