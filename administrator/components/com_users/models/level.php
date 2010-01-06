<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.access.helper');

/**
 * User view level model.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersModelLevel extends JModelForm
{
	/**
	 * Method to auto-populate the model state.
	 */
	protected function _populateState()
	{
		$app = JFactory::getApplication('administrator');

		// Load the User state.
		if (!($pk = (int) $app->getUserState('com_users.edit.level.id'))) {
			$pk = (int) JRequest::getInt('id');
		}
		$this->setState('level.id', $pk);

		// Load the parameters.
		$params	= JComponentHelper::getParams('com_users');
		$this->setState('params', $params);
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 */
	protected function _prepareTable(&$table)
	{
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type 	$type 	 The table type to instantiate
	 * @param	string 	$prefix	 A prefix for the table class name. Optional.
	 * @param	array	$options Configuration array for model. Optional.
	 * @return	JTable	A database object
	*/
	public function getTable($type = 'Viewlevel', $prefix = 'JTable', $config = array())
	{
		$return = JTable::getInstance($type, $prefix, $config);
		return $return;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function &getItem($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int)$this->getState('level.id');
		$false	= false;

		// Get a row instance.
		$table = &$this->getTable();

		// Attempt to load the row.
		$return = $table->load($pk);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->setError($table->getError());
			return $false;
		}

		// Prime required properties.
		if (empty($table->id))
		{
			// Prepare data for a new record.
		}

		$value = JArrayHelper::toObject($table->getProperties(1), 'JObject');

		// Convert the params field to an array.
		$value->rules = json_decode($value->rules);

		return $value;
	}

	/**
	 * Method to get the record form.
	 *
	 * @return	mixed	JForm object on success, false on failure.
	 */
	public function getForm()
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = parent::getForm('level', 'com_users.level', array('array' => 'jform', 'event' => 'onPrepareForm'));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_users.edit.level.data', array());

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}

		return $form;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 */
	public function save($data)
	{
		// Initialise variables;
		$table		= $this->getTable();
		$pk			= (!empty($data['id'])) ? $data['id'] : (int)$this->getState('level.id');
		$isNew		= true;

		// Load the row if saving an existing record.
		if ($pk > 0) {
			$table->load($pk);
			$isNew = false;
		}

		// Bind the data.
		if (!$table->bind($data)) {
			$this->setError(JText::sprintf('JTable_Error_Bind_failed', $table->getError()));
			return false;
		}

		// Prepare the row for saving
		$this->_prepareTable($table);

		// Check the data.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Store the data.
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		$this->setState('level.id', $table->id);

		return true;
	}

	/**
	 * Method to delete rows.
	 *
	 * @param	array	An array of item ids.
	 *
	 * @return	boolean	Returns true on success, false on failure.
	 */
	public function delete(&$pks)
	{
		// Typecast variable.
		$pks = (array) $pks;

		// Get a row instance.
		$table = &$this->getTable();

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				// Access checks.
				$allow = $user->authorise('core.edit.state', 'com_users');

				if ($allow)
				{
					if (!$table->delete($pk))
					{
						$this->setError($table->getError());
						return false;
					}
				}
				else
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JError_Core_Edit_State_not_permitted'));
				}
			}
			else
			{
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to adjust the ordering of a row.
	 *
	 * @param	int		The ID of the primary key to move.
	 * @param	integer	Increment, usually +1 or -1
	 * @return	boolean	False on failure or error, true otherwise.
	 */
	public function reorder($pk, $direction = 0)
	{
		// Sanitize the id and adjustment.
		$pk	= (!empty($pk)) ? $pk : (int) $this->getState('level.id');

		// Get an instance of the record's table.
		$table = $this->getTable();

		// Attempt to check-out and move the row.
		if (!$this->checkout($pk)) {
			return false;
		}

		// Load the row.
		if (!$table->load($pk)) {
			$this->setError($table->getError());
			return false;
		}

		// Access checks.
		$allow = $user->authorise('core.edit.state', 'com_users');

		if (!$allow)
		{
			$this->setError(JText::_('JError_Core_Edit_State_not_permitted'));
			return false;
		}

		// Move the row.
		// TODO: Where clause to restrict category.
		$table->move($pk);

		// Check-in the row.
		if (!$this->checkin($pk)) {
			return false;
		}

		return true;
	}

	/**
	 * Saves the manually set order of records.
	 *
	 * @param	array	An array of primary key ids.
	 * @param	int		+/-1
	 */
	function saveorder($pks, $order)
	{
		// Initialise variables.
		$table		= $this->getTable();
		$conditions	= array();

		if (empty($pks)) {
			return JError::raiseWarning(500, JText::_('JError_No_items_selected'));
		}

		// update ordering values
		foreach ($pks as $i => $pk)
		{
			$table->load((int) $pk);

			// Access checks.
			$allow = $user->authorise('core.edit.state', 'com_users');

			if (!$allow)
			{
				// Prune items that you can't change.
				unset($pks[$i]);
				JError::raiseWarning(403, JText::_('JError_Core_Edit_State_not_permitted'));
			}
			else if ($table->ordering != $order[$i])
			{
				$table->ordering = $order[$i];
				if (!$table->store())
				{
					$this->setError($table->getError());
					return false;
				}
				// remember to reorder this category
				$condition = 'catid = '.(int) $table->catid;
				$found = false;
				foreach ($conditions as $cond)
				{
					if ($cond[1] == $condition)
					{
						$found = true;
						break;
					}
				}
				if (!$found) {
					$conditions[] = array ($table->id, $condition);
				}
			}
		}

		// Execute reorder for each category.
		foreach ($conditions as $cond)
		{
			$table->load($cond[0]);
			$table->reorder($cond[1]);
		}

		return true;
	}
}
