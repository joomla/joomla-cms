<?php
/**
 * @version   0.0.2
 * @package   Babel-U-Lib
 * @copyright Copyright (C) 2011 - 2014 Mathew Lenning. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @author    Mathew Lenning - http://babel-university.com/
 */

// No direct access
defined('_JEXEC') or die;

abstract class Babelu_libModelRecord extends Babelu_libModelData
{
	/**
	 * Method to get a single record.
	 *
	 * @param   integer $pk The id of the primary key.
	 *
	 * @return  mixed    JObject on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItem($pk = null)
	{
		if (empty($pk))
		{
			$context = $this->getContext();
			$pk      = (int) $this->getState($context . '.id');
		}

		$activeRecord = $this->getActiveRecord($pk);

		// Convert to the JObject before adding other data.
		$properties = $activeRecord->getProperties(1);
		$item       = JArrayHelper::toObject($properties, 'JObject');

		if (property_exists($item, 'params'))
		{
			$registry = new JRegistry;
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}


		return $item;
	}
}