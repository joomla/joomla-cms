<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
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
		$app = JFactory::getApplication();

		// Get the form.
		try {
			$form = parent::getForm('com_users.level', 'level', array('control' => 'jform'));
		} catch (Exception $e) {
			$this->setError($e->getMessage());
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
			$this->setError($table->getError());
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
		$user = JFactory::getUser();

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
}
