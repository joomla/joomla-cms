<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');

/**
 * User Group model for Users.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersModelGroup extends JModelItem
{
	/**
	 * Array of items for memory caching.
	 *
	 * @var		array
	 */
	protected $_items			= array();

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return	void
	 */
	protected function _populateState()
	{
		$app		= &JFactory::getApplication('administrator');
		$params		= &JComponentHelper::getParams('com_users');

		// Load the group state.
		if (!$groupId = (int)$app->getUserState('com_users.edit.group.id')) {
			$groupId = (int)JRequest::getInt('group_id');
		}
		$this->setState('group.id', $groupId);

		// Add the group id to the context to preserve sanity.
		$context = 'com_users.group.'.$groupId.'.';

		// Load the parameters.
		$this->setState('params', $params);
	}

	/**
	 * Method to get a group item.
	 *
	 * @param	integer	The id of the group to get.
	 * @return	mixed	Group data object on success, false on failure.
	 */
	public function &getItem($groupId = null)
	{
		// Initialize variables.
		$groupId = (!empty($groupId)) ? $groupId : (int)$this->getState('group.id');
		$false	= false;

		// Get a level row instance.
		$table = &$this->getTable('Usergroup', 'JTable');

		// Attempt to load the row.
		$return = $table->load($groupId);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->serError($table->getError());
			return $false;
		}

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return $false;
		}

		$value = JArrayHelper::toObject($table->getProperties(1), 'JObject');

		//
		// Perform the rule pivot for read
		//

		// @todo Maybe this can going into a the JAccessHelper??
		jimport('joomla.access.helper');
		jimport('joomla.access.permission.simplerule');

		$value->actions = array();

		// Get all the core type 1 rules
		$actions	= JAccessHelper::getActions('core', 1);

		// Load the Simple Rule model
		$rule		= JSimpleRule::getInstance();

		foreach ($actions as $action)
		{
			$rule->load($action->name);

			// Get the user groups mapped to this rule
			$groups		= $rule->getUserGroups();

			// Is this group in this rule
			if (in_array($groupId, $groups)) {
				$value->actions[] = $action->id;
			}
		}

		return $value;
	}

	/**
	 * Method to get the group form.
	 *
	 * @return	mixed	JForm object on success, false on failure.
	 * @since	1.0
	 */
	public function &getForm()
	{
		// Initialize variables.
		$app	= &JFactory::getApplication();
		$false	= false;

		// Get the form.
		jimport('joomla.form.form');
		JForm::addFormPath(JPATH_COMPONENT.'/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT.'/models/fields');
		$form = &JForm::getInstance('jform', 'group', true, array('array' => true));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return $false;
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
		$groupId = (!empty($data['id'])) ? $data['id'] : (int)$this->getState('group.id');
		$isNew	= true;

		// Get a group row instance.
		$table = &$this->getTable('Usergroup', 'JTable');

		// Load the row if saving an existing item.
		if ($groupId > 0) {
			$table->load($groupId);
			$isNew = false;
		}

		// Bind the data.
		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		// Check the data.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Store the data.
		if (!$table->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		$groupId = $table->id;

		//
		// Perform the rule pivot for write
		//

		// @todo Maybe this can going into a the JAccessHelper??
		jimport('joomla.access.helper');
		jimport('joomla.access.permission.simplerule');

		if (!isset($data['actions'])) {
			$data['actions'] = array();
		}

		// Get all the core type 1 rules
		$actions	= JAccessHelper::getActions('core', 1);

		// Load the Simple Rule model
		$rule		= JSimpleRule::getInstance();

		foreach ($actions as $action)
		{
			$rule->load($action->name);

			// Get the user groups mapped to this rule
			$groups		= $rule->getUserGroups();

			// Is this action selected
			$hasAction	= in_array($action->id, $data['actions']);

			// Is this group in this rule
			$inGroup	= in_array($groupId, $groups);
			$changed		= false;

			if ($hasAction && !$inGroup) {
				// Need to add this user group to the rule
				$groups[]	= $groupId;
				$changed	= true;
			}
			else if (!$hasAction && $inGroup) {
				// Need to remove this user group from the rule
				$k			= array_search($groupId, $groups);
				array_splice($groups, $k, 1);
				$changed	= true;
			}

			if ($changed)
			{
				$rule->setUserGroups($groups);
				if (!$rule->store()) {
					$this->setError($rule->getErrorMsg());
					return false;
				}
			}
		}

		return $table->id;
	}

	/**
	 * Method to delete groups.
	 *
	 * @param	array	An array of group ids.
	 * @return	boolean	Returns true on success, false on failure.
	 */
	public function delete($groupIds)
	{
		// Sanitize the ids.
		$groupIds = (array) $groupIds;
		JArrayHelper::toInteger($groupIds);

		// Get a group row instance.
		$table = &$this->getTable('Usergroup', 'JTable');

		// Iterate the items to delete each one.
		foreach ($groupIds as $groupId)
		{
			$table->delete($groupId);
		}

		// Rebuild the nested set tree.
		$table->rebuild();

		return true;
	}
}
