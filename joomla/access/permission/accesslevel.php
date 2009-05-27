<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Access
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('JPATH_BASE') or die;

jimport('joomla.application.component.model');
jimport('joomla.database.query');

/*
 *	1 Rule
 *	1 Action
 *	1 Asset Group
 *	N User Groups
 *	N Users
 */

/**
 * Access Level model.
 *
 * @package 	Joomla.Framework
 * @subpackage	Access
 * @since		1.6
 */
class JAccessLevel extends JModel
{
	/**
	 * Model name.
	 */
	var $_name = 'AccessLevel';

	/**
	 * Associated access section id.
	 *
	 * @var	integer
	 */
	var $_section_id;

	/**
	 * Associated access section name.
	 *
	 * @var	string
	 */
	var $_section_name;

	/**
	 * Associated access rule id.
	 *
	 * @var	integer
	 */
	var $_rule_id;

	/**
	 * Associated access rule name.
	 *
	 * @var	string
	 */
	var $_rule_name;

	/**
	 * Associated action id.
	 *
	 * @var	integer
	 */
	var $_action_id;

	/**
	 * Associated action name.
	 *
	 * @var	string
	 */
	var $_action_name;

	/**
	 * Associated asset group id.
	 *
	 * @var	integer
	 */
	var $_asset_group_id;

	/**
	 * Associated asset group name.
	 *
	 * @var	string
	 */
	var $_asset_group_name;

	/**
	 * Associated user groups.
	 *
	 * @var	array
	 */
	var $_user_groups = array();

	/**
	 * Associated users.
	 *
	 * @var	array
	 */
	var $_users = array();

	/**
	 * Method to get the access level section id.
	 *
	 * @access	public
	 * @return	integer	Access section id.
	 * @since	1.0
	 */
	function getSectionId()
	{
		return $this->_section_id;
	}

	/**
	 * Method to get the access level section name.
	 *
	 * @access	public
	 * @return	string	Access section name.
	 * @since	1.0
	 */
	function getSectionName()
	{
		return $this->_section_name;
	}

	/**
	 * Method to set the access level section.
	 *
	 * @access	public
	 * @param	mixed	Access section name or id.
	 * @return	integer	Previous access section id.
	 * @since	1.0
	 */
	function setSection($section)
	{
		// Get the old section id.
		$old = $this->_section_id;

		// Load the action by name if necessary.
		if (!is_int($section))
		{
			$db = & $this->getDbo();
			$db->setQuery(
				'SELECT `id`' .
				' FROM `#__access_sections`' .
				' WHERE `name` = '.$db->Quote($section)
			);
			$sectionId = (int) $db->loadResult();
		}
		else {
			$sectionId = (int) $section;
			$db = & $this->getDbo();
			$db->setQuery(
				'SELECT `name`' .
				' FROM `#__access_sections`' .
				' WHERE `id` = '.(int)$sectionId
			);
			$section = $db->loadResult();
		}

		// Set the new action id and name.
		$this->_section_id = $sectionId;
		$this->_section_name = $section;

		return $old;
	}

	/**
	 * Method to get the access level rule.
	 *
	 * @access	public
	 * @return	integer	Access rule id.
	 * @since	1.0
	 */
	function getRule()
	{
		return $this->_rule_id;
	}

	/**
	 * Method to get the access level action id.
	 *
	 * @access	public
	 * @return	integer	Access action id.
	 * @since	1.0
	 */
	function getActionId()
	{
		return $this->_action_id;
	}

	/**
	 * Method to get the access level action name.
	 *
	 * @access	public
	 * @return	string	Access action name.
	 * @since	1.0
	 */
	function getActionName()
	{
		return $this->_action_name;
	}

	/**
	 * Method to set the access level action.
	 *
	 * @access	public
	 * @param	mixed	Access action name or id.
	 * @return	integer	Previous access action id.
	 * @since	1.0
	 */
	function setAction($action)
	{
		// Get the old action id.
		$old = $this->_action_id;

		// Load the action by name if necessary.
		if (!is_int($action))
		{
			$db = & $this->getDbo();
			$db->setQuery(
				'SELECT `id`' .
				' FROM `#__access_actions`' .
				' WHERE `name` = '.$db->Quote($action)
			);
			$actionId = (int) $db->loadResult();
		}
		else {
			$actionId = (int) $action;
			$db = & $this->getDbo();
			$db->setQuery(
				'SELECT `name`' .
				' FROM `#__access_actions`' .
				' WHERE `id` = '.(int) $actionId
			);
			$action = $db->loadResult();
		}

		// Set the new action id and name.
		$this->_action_id = $actionId;
		$this->_action_name = $action;

		return $old;
	}

	/**
	 * Method to get the access level asset group id.
	 *
	 * @access	public
	 * @return	integer	Access asset group id.
	 * @since	1.0
	 */
	function getAssetGroupId()
	{
		return $this->_asset_group_id;
	}

	/**
	 * Method to get the access level asset group name.
	 *
	 * @access	public
	 * @return	string	Access asset group name.
	 * @since	1.0
	 */
	function getAssetGroupName()
	{
		return $this->_asset_group_name;
	}

	/**
	 * Method to set the access level asset group.
	 *
	 * @access	public
	 * @param	mixed	Access asset group name or id.
	 * @return	integer	Previous access asset group id.
	 * @since	1.0
	 */
	function setAssetGroup($assetGroup)
	{
		// Get the old asset group id.
		$old = $this->_asset_group_id;

		// Load the asset group id by name if necessary.
		if (!is_int($assetGroup))
		{
			$db = & $this->getDbo();
			$db->setQuery(
				'SELECT `id`' .
				' FROM `#__access_assetgroups`' .
				' WHERE `title` = '.$db->Quote($assetGroup) .
				' AND `section_id` = '. (int) $this->_section_id
			);
			$assetGroupId = (int) $db->loadResult();
		}
		else {
			$assetGroupId = (int) $assetGroup;
			$db = & $this->getDbo();
			$db->setQuery(
				'SELECT `title`' .
				' FROM `#__access_assetgroups`' .
				' WHERE `id` = '.(int) $assetGroupId
			);
			$assetGroup = $db->loadResult();
		}

		// Set the new asset group id.
		$this->_asset_group_id = $assetGroupId;
		$this->_asset_group_name = $assetGroup;

		return $old;
	}

	/**
	 * Method to get the user groups for the access level.
	 *
	 * @access	public
	 * @return	array	User group ids.
	 * @since	1.0
	 */
	function getUserGroups()
	{
		return $this->_user_groups;
	}

	/**
	 * Method to set the access level user groups.
	 *
	 * @access	public
	 * @param	array	User group ids.
	 * @return	array	Previous user group ids.
	 * @since	1.0
	 */
	function setUserGroups($groups)
	{
		// If no groups are set, return false.
		if (empty($groups)) {
			return false;
		}

		// Get the old user groups.
		$old = $this->_user_groups;

		// Implode the group ids.
		$ids = implode(',', $groups);

		// Get the group ids that exist in the database.
		$db = & $this->getDbo();
		$db->setQuery(
			'SELECT `id`' .
			' FROM `#__usergroups`' .
			' WHERE `id` IN ('.$ids.')'
		);
		$actualGroups = (array) $db->loadResultArray();

		// Get the groups that are actually available in the database.
		$groups = array_intersect($groups, $actualGroups);

		// Set the new user groups.
		$this->_user_groups = $groups;

		return $old;
	}

	/**
	 * Method to get the users for the access level.
	 *
	 * @access	public
	 * @return	array	User ids.
	 * @since	1.0
	 */
	function getUsers()
	{
		return $this->_users;
	}

	/**
	 * Method to set the access level users.
	 *
	 * @access	public
	 * @param	array	User ids.
	 * @return	array	Previous user ids.
	 * @since	1.0
	 */
	function setUsers($users)
	{
		// If no users are set, return false.
		if (empty($users)) {
			return false;
		}

		// Get the old users.
		$old = $this->_users;

		// Implode the user ids.
		$ids = implode(',', $users);

		// Get the user ids that exist in the database.
		$db = & $this->getDbo();
		$db->setQuery(
			'SELECT `id`' .
			' FROM `#__users`' .
			' WHERE `id` IN ('.$ids.')'
		);
		$actualUsers = (array) $db->loadResultArray();

		// Get the users that are actually available in the database.
		$users = array_intersect($users, $actualUsers);

		// Set the new users.
		$this->_users = $users;

		return $old;
	}

	function bind()
	{
		// Method to bind data to the access level object.
	}

	function delete($title, $section, $action = 'core.view')
	{
		// Load the access level values.
		$this->load($title, $section, $action);

		/*
		 * Validate the object values.
		 */

		// Verify an access section.
		if (empty($this->_section_id)) {
			$this->setError(JText::_('Access_Section_Invalid'));
			return false;
		}

		// Verify an access action.
		if (empty($this->_action_id)) {
			$this->setError(JText::_('Access_Action_Invalid'));
			return false;
		}

		// Verify an access assetgroup.
		if (empty($this->_asset_group_id)) {
			$this->setError(JText::_('Access_Asset_Group_Invalid'));
			return false;
		}

		// Verify an access rule.
		if (empty($this->_rule_id)) {
			$this->setError(JText::_('Access_Rule_Invalid'));
			return false;
		}

		// Get a database object.
		$db = &$this->getDbo();

		// Delete any action maps for this rule.
		$db->setQuery(
			'DELETE FROM `#__access_action_rule_map`' .
			' WHERE `rule_id` = '.(int) $this->_rule_id
		);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Delete any asset group maps for this rule.
		$db->setQuery(
			'DELETE FROM `#__access_assetgroup_rule_map`' .
			' WHERE `rule_id` = '.(int) $this->_rule_id
		);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Delete any usergroup maps for this rule.
		$db->setQuery(
			'DELETE FROM `#__usergroup_rule_map`' .
			' WHERE `rule_id` = '.(int) $this->_rule_id
		);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Delete any user maps for this rule.
		$db->setQuery(
			'DELETE FROM `#__user_rule_map`' .
			' WHERE `rule_id` = '.(int) $this->_rule_id
		);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Delete this rule.
		$db->setQuery(
			'DELETE FROM `#__access_rules`' .
			' WHERE `id` = '.(int) $this->_rule_id
		);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		return true;
	}

	function load($title, $section, $action = 'core.view')
	{
		// Load the section
		$this->setSection($section);
		if (empty($this->_section_id)) {
			$this->setError(JText::_('Access_Section_Invalid'));
			return false;
		}

		// Load the action by convention always.
		$this->setAction($action);
		if (empty($this->_action_id)) {
			$this->setError(JText::_('Access_Action_Invalid'));
			return false;
		}

		// Load the asset group.
		$this->setAssetGroup($title);
		if (empty($this->_asset_group_id)) {
			$this->setError(JText::_('Access_Asset_Group_Invalid'));
			return false;
		}

		// Build the rule name by convention based on the action name 'core.view' and assetgroup id.
		$this->_rule_name = $this->_action_name.'.'.$this->_asset_group_id;

		// Attempt to load the rule id.
		$db = & $this->getDbo();
		$db->setQuery(
			'SELECT `id`' .
			' FROM `#__access_rules`' .
			' WHERE `name` = '.$db->Quote($this->_rule_name) .
			' AND `section_id` = '.(int) $this->_section_id
		);
		$ruleId = (int) $db->loadResult();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Set the rule id.
		$this->_rule_id = $ruleId;

		// Load the usergroups for the rule.
		$db->setQuery(
			'SELECT `group_id`' .
			' FROM `#__usergroup_rule_map`' .
			' WHERE `rule_id` = '.(int) $this->_rule_id
		);
		$groups = (array) $db->loadResultArray();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Set the user groups.
		$this->_user_groups = $groups;

		// Load the users for the rule.
		$db->setQuery(
			'SELECT `user_id`' .
			' FROM `#__user_rule_map`' .
			' WHERE `rule_id` = '.(int) $this->_rule_id
		);
		$users = (array) $db->loadResultArray();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Set the user groups.
		$this->_users = $users;

		return true;
	}

	function store()
	{
		/*
		 * Validate the object values.
		 */

		// Verify an access section.
		if (empty($this->_section_id)) {
			$this->setError(JText::_('Access_Section_Invalid'));
			return false;
		}

		// Verify an access action.
		if (empty($this->_action_id)) {
			$this->setError(JText::_('Access_Action_Invalid'));
			return false;
		}

		// Verify an access assetgroup.
		if (empty($this->_asset_group_id)) {
			$this->setError(JText::_('Access_Asset_Group_Invalid'));
			return false;
		}

		// Build the rule name based on the action name and assetgroup id.
		$this->_rule_name = $this->_action_name.'.'.$this->_asset_group_id;

		// Check to see if a rule already exists for this access level.
		$db = &$this->getDbo();
		$db->setQuery(
			'SELECT `id`' .
			' FROM `#__access_rules`' .
			' WHERE `name` = '.$db->Quote($this->_rule_name) .
			' AND `section_id` = '.(int) $this->_section_id
		);
		$ruleId = (int) $db->loadResult();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// If the rule doesn't exist, create it.
		if (empty($ruleId))
		{
			// Insert the rule into the database.
			$db->setQuery(
				'INSERT INTO `#__access_rules` (`section_id`, `section`, `name`, `title`, `allow`, `enabled`, `access_type`) VALUES' .
				' ('.(int) $this->_section_id.', '.$db->Quote($this->_section_name).', '.$db->Quote($this->_rule_name).', "SYSTEM", 1, 1, 3)'
			);
			$db->query();

			// Check for a database error.
			if ($db->getErrorNum()) {
				$this->setError($db->getErrorMsg());
				return false;
			}

			// Get the id of the rule just inserted.
			$ruleId = $db->insertid();
		}

		// Set the rule id.
		$this->_rule_id = (int) $ruleId;

		// Map the assetgroup to the rule.
		$db->setQuery(
			'REPLACE INTO `#__access_assetgroup_rule_map` (`group_id`, `rule_id`) VALUES' .
			' ('.(int) $this->_asset_group_id.', '.(int) $this->_rule_id.')'
		);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Map the action to the rule.
		$db->setQuery(
			'REPLACE INTO `#__access_action_rule_map` (`action_id`, `rule_id`) VALUES' .
			' ('.(int) $this->_action_id.', '.(int) $this->_rule_id.')'
		);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Delete existing usergroup maps to the rule.
		$db->setQuery(
			'DELETE FROM `#__usergroup_rule_map`' .
			' WHERE `rule_id` = '.(int) $this->_rule_id
		);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Map the usergroups to the rule if any exist.
		if (!empty($this->_user_groups))
		{
			// Build the values clause for the insert query.
			$values = array();
			foreach ($this->_user_groups as $group)
			{
				$values[] = '('.(int) $group.', '.(int) $this->_rule_id.')';
			}

			// Perform the insert query.
			$db->setQuery(
				'INSERT INTO `#__usergroup_rule_map` (`group_id`, `rule_id`) VALUES ' .
				implode(', ', $values)
			);
			$db->query();

			// Check for a database error.
			if ($db->getErrorNum()) {
				$this->setError($db->getErrorMsg());
				return false;
			}
		}

		// Delete existing user maps to the rule.
		$db->setQuery(
			'DELETE FROM `#__user_rule_map`' .
			' WHERE `rule_id` = '.(int) $this->_rule_id
		);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Map the users to the rule if any exist.
		if (!empty($this->_users))
		{
			// Build the values clause for the insert query.
			$values = array();
			foreach ($this->_users as $user)
			{
				$values[] = '('.(int) $user.', '.(int) $this->_rule_id.')';
			}

			// Perform the insert query.
			$db->setQuery(
				'INSERT INTO `#__user_rule_map` (`user_id`, `rule_id`) VALUES ' .
				implode(', ', $values)
			);
			$db->query();

			// Check for a database error.
			if ($db->getErrorNum()) {
				$this->setError($db->getErrorMsg());
				return false;
			}
		}

		return true;
	}
}
