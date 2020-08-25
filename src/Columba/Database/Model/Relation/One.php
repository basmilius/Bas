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

	private string $referenceKey;
	private string $selfKey;

	/**
	 * One constructor.
	 *
	 * @param Model|string $referenceModel
	 * @param string|null $selfKey
	 * @param string|null $referenceKey
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(string $referenceModel, ?string $selfKey = null, ?string $referenceKey = null)
	{
		parent::__construct($referenceModel);

		$this->referenceKey = $referenceKey ?? $referenceModel::column('id');
		$this->selfKey = $selfKey ?? $referenceModel::table() . '_id';
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function get(): ?Model
	{
		if ($this->referenceModel::column($this->referenceModel::primaryKey()) === $this->referenceKey)
		{
			$cache = $this->referenceModel::connection()->getCache();
			$key = $this->model->getValue($this->selfKey);

			if ($key === null || $key === 0)
				return null;

			if ($cache->has($key, $this->referenceModel))
				return $cache->get($key, $this->referenceModel);
		}

		return $this->single();
	}

	/**
	 * Gets the referenced key.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function getReferenceKey(): string
	{
		return $this->referenceKey;
	}

	/**
	 * Gets the model key.
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public final function getSelfKey(): string
	{
		return $this->selfKey;
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function relevantColumns(): array
	{
		return [$this->selfKey];
	}

	/**
	 * {@inheritDoc}
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function buildBaseQuery(): Builder
	{
		return $this->where($this->referenceKey, $this->model->getValue($this->selfKey) ?? 0);
	}

}
