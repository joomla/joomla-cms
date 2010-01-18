<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * User group model.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersModelGroup extends JModelForm
{
	/**
	 * Method to auto-populate the model state.
	 */
	protected function _populateState()
	{
		$app = JFactory::getApplication('administrator');

		// Load the User state.
		if (!($pk = (int) $app->getUserState('com_users.edit.group.id'))) {
			$pk = (int) JRequest::getInt('id');
		}
		$this->setState('group.id', $pk);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_users');
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
	public function getTable($type = 'Usergroup', $prefix = 'JTable', $config = array())
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
		$pk = (!empty($pk)) ? $pk : (int)$this->getState('group.id');
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
		$form = parent::getForm('group', 'com_users.group', array('array' => 'jform', 'event' => 'onPrepareForm'));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_users.edit.group.data', array());

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
		$dispatcher = JDispatcher::getInstance();
		$table		= $this->getTable();
		$pk			= (!empty($data['id'])) ? $data['id'] : (int) $this->getState('group.id');
		$isNew		= true;

		// Include the content plugins for events.
		JPluginHelper::importPlugin('user');

		// Load the row if saving an existing record.
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError(JText::sprintf('JTable_Error_Bind_failed', $table->getError()));
			return false;
		}

		// Prepare the row for saving.
		$this->_prepareTable($table);

		// Check the data.
		if (!$table->check())
		{
			$this->setError($table->getError());
			return false;
		}

		// Trigger the onBeforeSaveContent event.
		$result = $dispatcher->trigger('onBeforeStoreUsergroup', array(&$table, $isNew));
		if (in_array(false, $result, true)) {
			$this->setError($table->getError());
			return false;
		}

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());
			return false;
		}

		// Trigger the onAftereStoreUser event
		$dispatcher->trigger('onAfterStoreUsergroup', array($table->getProperties(), $isNew, true, null));

		$this->setState('group.id', $table->id);

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

		// Trigger the onBeforeStoreUser event.
		JPluginHelper::importPlugin('user');
		$dispatcher = &JDispatcher::getInstance();

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				// Access checks.
				$allow = $user->authorise('core.edit.state', 'com_users');

				if ($allow)
				{
					// Fire the onBeforeDeleteUser event.
					$dispatcher->trigger('onBeforeDeleteUser', array($table->getProperties()));

					if (!$table->delete($pk))
					{
						$this->setError($table->getError());
						return false;
					}
					else
					{
						// Trigger the onAfterDeleteUsergroup event.
						$dispatcher->trigger('onAfterDeleteUsergroup', array($user->getProperties(), true, $this->getError()));
					}
				}
				else
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JError_Core_Delete_not_permitted'));
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
