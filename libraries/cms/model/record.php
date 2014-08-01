<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Base Cms Model Class
 *
 * @package     Joomla.Libraries
 * @subpackage  Model
 * @since       3.4
 */
abstract class JModelRecord extends JModelData
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