<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Invalid Request.');

jimport('joomla.application.component.model');

/**
 * Member model for Members.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_members
 * @version		1.6
 */
class MembersModelMember extends JModel
{
	/**
	 * Flag to indicate model state initialization.
	 *
	 * @access	protected
	 * @var		boolean
	 */
	var $__state_set		= null;

	/**
	 * Override constructor
	 *
	 * @access	public
	 * @param	array	Configuration array
	 */
	function __construct($config = array())
	{
		if (!empty($config['ignore_request'])) {
			$this->__state_set = true;
		}
		parent::__construct($config);
	}

	/**
	 * Overridden method to get model state variables.
	 *
	 * @access	public
	 * @param	string	$property	Optional parameter name.
	 * @return	object	The property where specified, the state object where omitted.
	 * @since	1.0
	 */
	function getState($property = null)
	{
		if (!$this->__state_set)
		{
			$app		= &JFactory::getApplication('administrator');
			$params		= &JComponentHelper::getParams('com_members');

			// Load the Member state.
			if (JRequest::getWord('layout') === 'edit') {
				$member_id = (int)$app->getUserState('com_members.edit.member.id');
				$this->setState('member.id', $member_id);
			} else {
				$member_id = (int)JRequest::getInt('member_id');
				$this->setState('member.id', $member_id);
			}

			// Add the Member id to the context to preserve sanity.
			$context	= 'com_members.member.'.$member_id.'.';

			// Load the parameters.
			$this->setState('params', $params);

			$this->__state_set = true;
		}

		return parent::getState($property);
	}

	/**
	 * Method to get a member item.
	 *
	 * @access	public
	 * @param	integer	The id of the member to get.
	 * @return	mixed	Member data object on success, false on failure.
	 * @since	1.0
	 */
	function &getItem($userId = null)
	{
		// Initialize variables.
		$userId = (!empty($userId)) ? $userId : (int)$this->getState('member.id');
		$false	= false;

		// Get a member row instance.
		$table = &$this->getTable('User', 'JTable');

		// Attempt to load the row.
		$return = $table->load($userId);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->setError($table->getError());
			return $false;
		}

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return $false;
		}

		$value = JArrayHelper::toObject($table->getProperties(1), 'JObject');
		$value->profile = new JObject;

		// Get the dispatcher and load the members plugins.
		$dispatcher	= &JDispatcher::getInstance();
		JPluginHelper::importPlugin('user');

		// Trigger the data preparation event.
		$results = $dispatcher->trigger('onPrepareUserProfileData', array($userId, &$value));

		return $value;
	}

	/**
	 * Method to get the group form.
	 *
	 * @access	public
	 * @return	mixed	JForm object on success, false on failure.
	 * @since	1.0
	 */
	function &getForm()
	{
		// Initialize variables.
		$app	= &JFactory::getApplication();
		$false	= false;

		// Get the form.
		jimport('joomla.form.form');
		JForm::addFormPath(JPATH_COMPONENT.'/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT.'/models/fields');
		$form = &JForm::getInstance('jform', 'member', true, array('array' => true));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return $false;
		}

		// Get the dispatcher and load the members plugins.
		$dispatcher	= &JDispatcher::getInstance();
		JPluginHelper::importPlugin('user');

		// Trigger the form preparation event.
		$results = $dispatcher->trigger('onPrepareUserProfileForm', array($this->getState('member.id'), &$form));

		// Check for errors encountered while preparing the form.
		if (count($results) && in_array(false, $results, true)) {
			$this->setError($dispatcher->getError());
			return $false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_members.edit.member.data', array());

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}

		return $form;
	}

	/**
	 * Gets the available groups.
	 */
	function getGroups()
	{
		$model = JModel::getInstance('Groups', 'MembersModel');
		return $model->getItems();
	}

	/**
	 * Gets the groups this object is assigned to
	 */
	function getAssignedGroups($userId = null)
	{
		// Initialize variables.
		$userId = (!empty($userId)) ? $userId : (int)$this->getState('member.id');

		jimport('joomla.user.helper');
		$result = JUserHelper::getUserGroups($userId);

		return $result;
	}

	/**
	 * Method to checkin a row.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	integer	$id		The numeric id of a row
	 * @return	boolean	True on success/false on failure
	 */
	function checkin($userId = null)
	{
		// Initialize variables.
		$userId = (!empty($userId)) ? $userId : (int)$this->getState('member.id');
		$user		= &JFactory::getUser();
		$user_id	= (int) $user->get('id');

		if (!$userId) {
			return true;
		}

		// Get a JTableUser instance.
		$member = &JTable::getInstance('User', 'JTable');

		// Attempt to check-in the row.
		$return = $member->checkin($user_id, $userId);

		// Check for a database error.
		if ($return === false) {
			$this->setError($member->getError());
			return false;
		}

		return true;
	}

	/**
	 * Method to check-out a Member for editing.
	 *
	 * @access	public
	 * @param	int		$member_id	The numeric id of the Member to check-out.
	 * @return	bool	False on failure or error, success otherwise.
	 * @since	1.0
	 */
	function checkout($userId)
	{
		// Initialize variables.
		$userId = (!empty($userId)) ? $userId : (int)$this->getState('member.id');
		$user		= &JFactory::getUser();
		$user_id	= (int) $user->get('id');

		// Check for a new Member id.
		if ($userId === -1) {
			return true;
		}

		// Get a JTableUser instance.
		$member = &JTable::getInstance('User', 'JTable');

		// Attempt to check-out the row.
		$return = $member->checkout($user_id, $userId);

		// Check for a database error.
		if ($return === false) {
			$this->setError($member->getError());
			return false;
		}

		// Check if the row is checked-out by someone else.
		if ($return === null) {
			$this->setError(JText::_('MEMBERS_MEMBER_CHECKED_OUT'));
			return false;
		}

		return true;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @access	public
	 * @param	array	The form data.
	 * @return	mixed	Array of filtered data if valid, false otherwise.
	 * @since	1.0
	 */
	function validate($data)
	{
		// Get the form.
		$form = &$this->getForm();

		// Check for an error.
		if ($form === false) {
			return false;
		}

		// Filter and validate the form data.
		$data	= $form->filter($data);
		$return	= $form->validate($data);

		// Check for an error.
		if (JError::isError($return)) {
			$this->setError($return->getMessage());
			return false;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message) {
				$this->setError($message);
			}

			return false;
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @access	public
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.0
	 */
	function save($data)
	{
		$userId = (!empty($data['id'])) ? $data['id'] : (int)$this->getState('member.id');
		$isNew	= true;

		// Get a JTableUser instance.
		$user = &JUser::getInstance(0);

		// Load the row if saving an existing item.
		if ($userId > 0) {
			$user->load($userId);
			$isNew = false;
		}

		// The password field is a special case.
		if (!empty($data['password']))
		{
			// If a password was sent, ensure that it was verified.
			if ($data['password'] != $data['password2']) {
				$this->setError('MEMBERS_PASSWORD_MISMATCH');
				return false;
			}

			// Generate a password hash.
			jimport('joomla.user.helper');
			$salt  = JUserHelper::genRandomPassword(32);
			$crypt = JUserHelper::getCryptedPassword($data['password'], $salt);
			$data['password'] = $crypt.':'.$salt;
		}
		else {
			// Do nothing to the password field.
			unset($data['password']);
		}

		// Bind the data.
		if (!$user->bind($data)) {
			$this->setError($user->getError());
			return false;
		}

		// Ensure the legacy group is added to the user groups.
		switch ($user->gid)
		{
			case 18:
				$gid = 2;
				break;
			case 19:
				$gid = 3;
				break;
			case 20:
				$gid = 4;
				break;
			case 21:
				$gid = 5;
				break;
			case 23:
				$gid = 6;
				break;
			case 24:
				$gid = 7;
				break;
			case 25:
				$gid = 8;
				break;
		}
		if (!array_key_exists($gid, $user->groups)) {
			$user->groups[$gid] = 'Legacy';
		}

		// Get the old user
		$old = JUser::getInstance($userId);

		// Fire the onBeforeStoreUser event.
		JPluginHelper::importPlugin('user');
		$dispatcher =& JDispatcher::getInstance();
		$dispatcher->trigger('onBeforeStoreUser', array($old->getProperties(), $isNew));

		// Store the data.
		if (!$result = $user->save()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Fire the onAftereStoreUser event
		$dispatcher->trigger('onAfterStoreUser', array($this->getProperties(), $isNew, $result, $this->getError()));

		return $user->id;
	}

	/**
	 * Perform batch operations
	 *
	 * @param	array	An array of variable for the batch operation
	 * @param	array	An array of IDs on which to operate
	 */
	function batch($config, $member_ids)
	{
		// Ensure there are selected members to operate on.
		if (empty($member_ids))
		{
			$this->setError(JText::_('MEMBERS_MEMBERS_NOT_SELECTED'));
			return false;
		}
		// Only run operations if a config array is present.
		else if (!empty($config))
		{
			// Ensure there is a valid group.
			$group_id = JArrayHelper::getValue($config, 'group_id', 0, 'int');
			if ($group_id < 1)
			{
				$this->setError(JText::_('MEMBERS_INVALID_GROUP'));
				return false;
			}

			// Get the system ACL object and set the mode to database driven.
			$acl = &JFactory::getACL();
			$oldAclMode = $acl->setCheckMode(1);

			$groupLogic	= JArrayHelper::getValue($config, 'group_logic');
			switch ($groupLogic)
			{
				case 'set':
					$doDelete 		= 2;
					$doAssign 		= true;
					break;

				case 'del':
					$doDelete		= true;
					$doAssign		= false;
					break;

				case 'add':
				default:
					$doDelete		= false;
					$doAssign		= true;
					break;
			}

			// Remove the users from the group(s) if requested.
			if ($doDelete)
			{
				// Purge operation, remove the users from all groups.
				if ($doDelete === 2)
				{
					$this->_db->setQuery(
						'DELETE FROM `#__core_acl_groups_aro_map`' .
						' WHERE `aro_id` IN ('.implode(',', $member_ids).')'
					);
				}
				// Remove the users from the group.
				else
				{
					$this->_db->setQuery(
						'DELETE FROM `#__core_acl_groups_aro_map`' .
						' WHERE `aro_id` IN ('.implode(',', $member_ids).')' .
						' AND `group_id` = '.$group_id
					);
				}

				// Check for database errors.
				if (!$this->_db->query()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}

			// Assign the users to the group if requested.
			if ($doAssign)
			{
				// Build the tuples array for the assignment query.
				$tuples = array();
				foreach ($member_ids as $id)
				{
					$tuples[] = '('.$id.','.$group_id.')';
				}

				$this->_db->setQuery(
					'INSERT IGNORE INTO `#__core_acl_groups_aro_map` (`aro_id`, `group_id`)' .
					' VALUES '.implode(',', $tuples)
				);

				// Check for database errors.
				if (!$this->_db->query()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}

			// Set the ACL mode back to it's previous state.
			$acl->setCheckMode($oldAclMode);
		}

		return true;
	}
}