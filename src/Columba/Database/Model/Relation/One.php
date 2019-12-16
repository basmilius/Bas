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

use Columba\Database\Model\Model;
use Columba\Database\Query\Builder\Builder;

/**
 * Class One
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Model\Relation
 * @since 1.6.0
 */
class One extends Relation
{

	private string $referencedKey;
	private string $selfKey;

	/**
	 * One constructor.
	 *
	 * @param Model|string $referencedModel
	 * @param string|null  $referencedKey
	 * @param string|null  $selfKey
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(string $referencedModel, ?string $referencedKey = null, ?string $selfKey = null)
	{
		parent::__construct($referencedModel);

		$this->referencedKey = $referencedKey ?? $referencedModel::table() . '_id';
		$this->selfKey = $selfKey ?? 'id';
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function get(): ?Model
	{
		return $this->collection()->first();
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function buildBaseQuery(): Builder
	{
		return $this->where($this->selfKey, $this->model[$this->referencedKey] ?? 0);
	}

}
