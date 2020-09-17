<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Model\DataModel\Relation;

defined('_JEXEC') || die;

use FOF30\Model\DataModel;
use FOF30\Model\DataModel\Collection;

/**
 * HasOne (straight 1-to-1) relation: this model is a parent which has exactly one child in the foreign table
 *
 * For example, parentModel is Users and foreignModel is Phones. Each uses has exactly one Phone.
 */
class HasOne extends HasMany
{
	/**
	 * Get the relation data.
	 *
	 * If you want to apply additional filtering to the foreign model, use the $callback. It can be any function,
	 * static method, public method or closure with an interface of function(DataModel $foreignModel). You are not
	 * supposed to return anything, just modify $foreignModel's state directly. For example, you may want to do:
	 * $foreignModel->setState('foo', 'bar')
	 *
	 * @param   callable    $callback  The callback to run on the remote model.
	 * @param   Collection  $dataCollection
	 *
	 * @return Collection|DataModel
	 */
	public function getData($callback = null, Collection $dataCollection = null)
	{
		if (is_null($dataCollection))
		{
			return parent::getData($callback, $dataCollection)->first();
		}
		else
		{
			return parent::getData($callback, $dataCollection);
		}
	}
}
