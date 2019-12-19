<?php
/**
 * Copyright (c) 2017 - 2019 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Latte Framework package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Columba\Database\Model\Relation;

use Columba\Data\Collection;
use Columba\Database\Model\Model;
use Columba\Database\Query\Builder\Builder;
use function Columba\Util\preDie;

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
	 * @param Model|string $referencedModel
	 * @param string       $linkingTable
	 * @param string       $selfKey
	 * @param string       $referenceKey
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(string $referencedModel, string $linkingTable, ?string $selfKey = null, ?string $referenceKey = null)
	{
		parent::__construct($referencedModel);

		$this->linkingTable = $linkingTable;
		$this->referenceKey = $referenceKey ?? $referencedModel::table() . '.id';
		$this->selfKey = $selfKey ?? $referencedModel::table() . '_id';
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
	protected function buildBaseQuery(): Builder
	{
		return $this
			->leftJoin($this->linkingTable, fn(Builder $query) => $query
				->on($this->referenceKey, '=', $this->referencedModel::column($this->referencedModel::primaryKey())))
			->where(Model::column($this->selfKey, $this->linkingTable), '=', $this->model[$this->model::primaryKey()]);
	}

}
