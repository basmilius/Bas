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
 * Class Relation
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Model\Relation
 * @since 1.6.0
 */
abstract class Relation extends Builder
{

	/** @var Model|string */
	protected string $referencedModel;
	protected ?Model $model = null;

	/**
	 * Relation constructor.
	 *
	 * @param Model|string $referencedModel
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(string $referencedModel)
	{
		parent::__construct();

		$this->referencedModel = $referencedModel;
	}

	/**
	 * Internal: Sets the base model.
	 *
	 * @param Model $model
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 * @codeCoverageIgnore
	 * @internal
	 */
	public function setModel(Model $model): void
	{
		$this->model = $model;
		$this->setConnection($this->model->getConnection());

		$this->reset();
		$this->model($this->referencedModel);
		$this->merge($this->referencedModel::select());
		$this->buildBaseQuery();
	}

	/**
	 * Returns the referenced object(s).
	 *
	 * @return Collection<Model>|Model|Model[]|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public abstract function get();

	/**
	 * Returns an array with relevant columns.
	 *
	 * @return string[]
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public abstract function relevantColumns(): array;

	/**
	 * Builds the base query.
	 *
	 * @return Builder
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected abstract function buildBaseQuery(): Builder;

}
