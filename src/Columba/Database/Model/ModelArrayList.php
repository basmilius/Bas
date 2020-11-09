<?php
declare(strict_types=1);

namespace Columba\Database\Model;

use Columba\Collection\ArrayList;

/**
 * Class ModelArrayList
 * 
 * @template T2 of Model
 * @template-covariant T2
 * @extends ArrayList<T2>
 *
 * @author Bas Milius <bas@mili.us>
 * @package Columba\Database\Model
 * @since 1.6.0
 */
class ModelArrayList extends ArrayList
{

	/**
	 * Marks the given columns as hidden for every model in the list.
	 *
	 * @param array $columns
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function makeHidden(array $columns): self
	{
		return $this->mapTransform(fn(Model $model) => $model->makeHidden($columns));
	}

	/**
	 * Marks the given columns as visible for every model in the list.
	 *
	 * @param array $columns
	 *
	 * @return $this
	 * @author Bas Milius <bas@mili.us>
	 * @since 1.6.0
	 */
	public function makeVisible(array $columns): self
	{
		return $this->mapTransform(fn(Model $model) => $model->makeVisible($columns));
	}

}
