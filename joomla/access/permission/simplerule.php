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
 *	1 Asset
 *	N User Groups
 *	N Users
 */

/**
 * Simple Rule model.
 *
 * @package 	Joomla.Framework
 * @subpackage	Access
 * @since		1.6
 */
class JSimpleRule extends JModel
{
	/**
	 * Model name.
	 */
	var $_name = 'SimpleRule';

	/**
	 * Associated access section.
	 *
	 * @var	integer
	 */
	var $_section_id;

	/**
	 * Associated access rule.
	 *
	 * @var	integer
	 */
	var $_rule_id;

	/**
	 * Associated action.
	 *
	 * @var	integer
	 */
	var $_action_id;

	/**
	 * Associated asset.
	 *
	 * @var	integer
	 */
	var $_asset_id;

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
	 * Instantiate
	 */
	public static function getInstance()
	{
		return new JSimpleRule;
	}

	/**
	 * Method to get the rule section.
	 *
	 * @access	public
	 * @return	integer	Access section id.
	 * @since	1.0
	 */
	function getSection()
	{
		return $this->_section_id;
	}

	/**
	 * Method to set the rule section.
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
			$db = &$this->getDbo();
			$db->setQuery(
				'SELECT `name`' .
				' FROM `#__access_sections`' .
				' WHERE `id` = '.(int) $sectionId
			);
			$section = $db->loadResult();
		}

		// Set the new action id and name.
		$this->_section_id = $sectionId;
		$this->_section_name = $section;

		return $old;
	}

	/**
	 * Method to get the rule id.
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
	 * Method to get the rule action id.
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
	 * Method to set the rule action.
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
			$db = &$this->getDbo();
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
	 * Method to get the rule asset.
	 *
	 * @access	public
	 * @return	integer	Access asset id.
	 * @since	1.0
	 */
	function getAsset()
	{
		return $this->_asset_id;
	}

	/**
	 * Method to set the rule asset.
	 *
	 * @access	public
	 * @param	mixed	Access asset name or id.
	 * @return	integer	Previous access asset id.
	 * @since	1.0
	 */
	function setAsset($asset)
	{
		// Get the old asset id.
		$old = $this->_asset_id;

		// Load the asset id by name if necessary.
		if (!is_int($asset))
		{
			$db = & $this->getDbo();
			$db->setQuery(
				'SELECT `id`' .
				' FROM `#__access_assets`' .
				' WHERE `name` = '.$db->Quote($asset) .
				' AND `section_id` = '. (int) $this->_section_id
			);
			$assetId = (int) $db->loadResult();
		}
		else {
			$assetId = (int) $asset;
			$db = &$this->getDbo();
			$db->setQuery(
				'SELECT `id`' .
				' FROM `#__access_assets`' .
				' WHERE `id` = '.$db->Quote($assetId) .
				' AND `section_id` = '. (int) $this->_section_id
			);
			$asset = $db->loadResult();
		}

		// Set the new asset id and name.
		$this->_asset_id = $assetId;
		$this->_asset_name = $asset;

		return $old;
	}

	/**
	 * Method to get the user groups for the rule.
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
	 * Method to set the rule user groups.
	 *
	 * @access	public
	 * @param	array	User group ids.
	 * @return	array	Previous user group ids.
	 * @since	1.0
	 */
	function setUserGroups($groups)
	{
		// Get the old user groups.
		$old = $this->_user_groups;

		// If no groups are set, return false.
		if (empty($groups)) {
			// @louis - needed to fix this to get the rule pivots to work
			//return false;
			$this->_user_groups = array();
			return $old;
		}

		// Implode the group ids.
		$ids = implode(',', $groups);

		// Get the group ids that exist in the database.
		$db = &$this->getDbo();
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
	 * Method to get the users for the rule.
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
	 * Method to set the rule users.
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
		// Method to bind data to the rule object.
	}

	function delete($action, $asset = null)
	{
		// Load the rule values.
		$this->load($action, $asset);

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

		// Verify an access rule.
		if (empty($this->_rule_id)) {
			$this->setError(JText::_('Access_Rule_Invalid'));
			return false;
		}

		// Get a database object.
		$db = & $this->getDbo();

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

		// Delete any asset maps for this rule.
		$db->setQuery(
			'DELETE FROM `#__access_asset_rule_map`' .
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

	function load($action, $asset = null)
	{
		// Load the action by convention always.
		$this->setAction($action);
		if (empty($this->_action_id)) {
			$this->setError(JText::_('Access_Action_Invalid'));
			return false;
		}

		$db = & $this->getDbo();
		$db->setQuery(
			'SELECT `section_id`' .
			' FROM `#__access_actions`' .
			' WHERE `id` = '.(int) $this->_action_id
		);
		$sectionId = (int) $db->loadResult();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Load the section
		$this->setSection($sectionId);
		if (empty($this->_section_id)) {
			$this->setError(JText::_('Access_Section_Invalid'));
			return false;
		}

		// Look for the optional asset.
		if (!is_null($asset))
		{
			// Load the asset.
			$this->setAsset($asset);
			if (empty($this->_asset_id)) {
				$this->setError(JText::_('Access_Asset_Invalid'));
				return false;
			}
		}

		// Build the rule name by convention based on the action name and asset id if present.
		if (!is_null($asset)) {
			$this->_rule_name = $this->_action_name.'.s.'.$this->_asset_id;
		} else {
			$this->_rule_name = $this->_action_name;
		}

		// Attempt to load the rule id.
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

		// Build the rule name and type by convention based on the action name and asset id if present.
		if (!empty($this->_asset_id)) {
			$this->_rule_name = $this->_action_name.'.s.'.$this->_asset_id;
			$this->_rule_type = 2;
		} else {
			$this->_rule_name = $this->_action_name;
			$this->_rule_type = 1;
		}

		// Check to see if a row already exists for this rule.
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
				' ('.(int) $this->_section_id.', '.$db->Quote($this->_section_name).', '.$db->Quote($this->_rule_name).', "SYSTEM", 1, 1, '.(int) $this->_rule_type.')'
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

		// Map the asset to the rule if present.
		if (!empty($this->_asset_id))
		{
			$db->setQuery(
				'REPLACE INTO `#__access_asset_rule_map` (`asset_id`, `rule_id`) VALUES' .
				' ('.(int) $this->_asset_id.', '.(int) $this->_rule_id.')'
			);
			$db->query();

			// Check for a database error.
			if ($db->getErrorNum()) {
				$this->setError($db->getErrorMsg());
				return false;
			}
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
