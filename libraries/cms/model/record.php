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
	 * @return  mixed  Item in stdClass on success, false on failure
	 *
	 * @since   3.4
	 */
	public function getItem($pk = null)
	{
		if (empty($pk))
		{
			$pk = (int) $this->getStateVar($this->getName() . '.id');
		}

		$activeRecord = $this->getActiveRecord($pk);

		// Convert to a stdClass before adding other data.
		$properties = $activeRecord->getProperties(1);
		$item       = JArrayHelper::toObject($properties);

		if (property_exists($item, 'params'))
		{
			$registry = new JRegistry;
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}

		return $item;
	}

	/**
	 * Method to increment the hit counter for the record
	 *
	 * @param   integer  $id  Optional ID of the record.
	 *
	 * @return  boolean  True on success
	 *
	 * @since  3.2
	 */
	public function hit($id = null)
	{
		$type = $this->getName();

		if (empty($id))
		{
			$id = $this->getStateVar($type . '.id');
		}

		$item = $this->getTable();

		return $item->hit($id);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @param string $ordering  column to order by. I.E. 'a.title'
	 * @param string $direction 'ASC' or 'DESC'
	 *
	 * @return  void
	 *
	 * @note    Calling getState in this method will result in recursion.
	 * @since   3.4
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		if (!$this->stateIsSet)
		{
			$key = $this->getTable()->getKeyName();

			// Get the pk of the record from the request.
			$pk = JFactory::getApplication()->input->getInt($key);
			$this->state->set($this->getName() . '.id', $pk);

			parent::populateState($ordering, $direction);
		}
	}
}
