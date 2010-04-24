<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * User group model.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersModelGroup extends JModelAdmin
{
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	*/
	public function getTable($type = 'Usergroup', $prefix = 'JTable', $config = array())
	{
		$return = JTable::getInstance($type, $prefix, $config);
		return $return;
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
		$form = parent::getForm('com_users.group', 'group', array('control' => 'jform'));
		if (empty($form)) {
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
			$this->setError($table->getError());
			return false;
		}

		// Prepare the row for saving.
		$this->prepareTable($table);

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
		$user	= JFactory::getUser();

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
