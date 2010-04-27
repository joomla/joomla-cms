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
	 * @var		string	The event to trigger after saving the data.
	 * @since	1.6
	 */
	protected $event_after_save = 'onAfterStoreUsergroup';

	/**
	 * @var		string	The event to trigger after before the data.
	 * @since	1.6
	 */
	protected $event_before_save = 'onBeforeStoreUsergroup';

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
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
	 * @since	1.6
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
		} else {
			$form->bind($this->getItem());
		}

		return $form;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function save($data)
	{
		// Include the content plugins for events.
		JPluginHelper::importPlugin('user');

		return parent::save($data);
	}

	/**
	 * Method to delete rows.
	 *
	 * @param	array	An array of item ids.
	 * @return	boolean	Returns true on success, false on failure.
	 * @since	1.6
	 */
	public function delete(&$pks)
	{
		// Typecast variable.
		$pks = (array) $pks;
		$user	= JFactory::getUser();

		// Get a row instance.
		$table = $this->getTable();

		// Trigger the onBeforeStoreUser event.
		JPluginHelper::importPlugin('user');
		$dispatcher = JDispatcher::getInstance();

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk) {
			if ($table->load($pk)) {
				// Access checks.
				$allow = $user->authorise('core.edit.state', 'com_users');

				if ($allow) {
					// Fire the onBeforeDeleteUser event.
					$dispatcher->trigger('onBeforeDeleteUser', array($table->getProperties()));

					if (!$table->delete($pk)) {
						$this->setError($table->getError());
						return false;
					} else {
						// Trigger the onAfterDeleteUsergroup event.
						$dispatcher->trigger('onAfterDeleteUsergroup', array($user->getProperties(), true, $this->getError()));
					}
				} else {
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JERROR_CORE_DELETE_NOT_PERMITTED'));
				}
			} else {
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}
}