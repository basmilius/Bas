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
	protected string $referenceModel;
	protected ?Model $model = null;

	/**
	 * Relation constructor.
	 *
	 * @param Model|string $referenceModel
	 *
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function __construct(string $referenceModel)
	{
		parent::__construct();

		$this->referenceModel = $referenceModel;
	}

	/**
	 * Gets the reference model.
	 *
	 * @return Model|string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public final function getReferenceModel(): string
	{
		return $this->referenceModel;
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
		$this->model($this->referenceModel);
		$this->merge($this->referenceModel::select());
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
	 * Returns relations that should be eager loaded.
	 *
	 * @return array|null
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.0.0
	 */
	public function getEagerLoad(): ?array
	{
		return null;
	}

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

	/**
	 * Returns the last part of a key.
	 *
	 * @param string $key
	 *
	 * @return string
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	protected function simpleKey(string $key): string
	{
		if (strstr($key, '`'))
		{
			$parts = explode('.', $key);
			$part = $parts[count($parts) - 1];
			$key = trim($part, '`');
		}

		return $key;
	}

}
