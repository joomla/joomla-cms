<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Access
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('JPATH_BASE') or die;

if (!defined('JPERMISSION_VIEW')) {
	define('JPERMISSION_VIEW', 3);
}
if (!defined('JPERMISSION_ASSET')) {
	define('JPERMISSION_ASSET', 2);
}
if (!defined('JPERMISSION_ACTION')) {
	define('JPERMISSION_ACTION', 1);
}

jimport('joomla.database.query');

/**
 * Class that handles all access authorization
 *
 * @package 	Joomla.Framework
 * @subpackage	User
 * @since		1.6
 */
class JAccess extends JObject
{
	var $_quiet = true;

	function quiet($value)
	{
		$old = $this->_quiet;
		$this->_quiet = (boolean) $value;
		return $old;
	}

	/**
	 * Method to check authorization for a user / action / asset combination.
	 *
	 * @access	public
	 * @param	integer	User id.
	 * @param	string	Action name.
	 * @param	string	Asset name.
	 * @return	boolean	True if authorized.
	 * @since	1.0
	 */
	public function check($userId, $actionName, $assetName = null)
	{
		// Sanitize inputs.
		$userId = (int) $userId;
		$actionName = strtolower(preg_replace('#[\s\-]+#', '.', trim($actionName)));
		$assetName  = strtolower(preg_replace('#[\s\-]+#', '.', trim($assetName)));

		// @todo More advanced caching to span session
		static $cache;

		// Simple cache
		if ($cache == null) {
			$cache = array();
		}

		$cacheId = $userId.'.'.$actionName.'.'.$assetName;

		if (!isset($cache[$cacheId]))
		{
			// This query is where all the magic happens.
			// The ordering is very important here, as well very tricky to get correct.
			// Currently there can be  duplicate Rules, or ones that step on each other toes.
			// In this case, the ACL that was last updated/created
			// is used.
			//
			// This is probably where the most optimizations can be made.

			$sqlUserGroupIds	= null;
			$sqlAssetGroupIds	= null;
			$assetId			= 0;

			$db = &JFactory::getDbo();

			// Find out the Id of the asset if supplied
			if ($assetName)
			{
				// We could add this to the main query but we need to Asset Id for another part of the query anyway
				$db->setQuery('SELECT id FROM #__access_assets WHERE `name` = '.$db->quote($assetName), 0, 1);
				$assetId = $db->loadResult();

				$this->_quiet or $this->_log($db->getQuery());
				$this->_quiet or $this->_log("Asset by name $assetName had is = ".$assetId);
			}

			// Start the query
			$query = new JQuery;

			// Select the Id of the rule, allow and the return value
			$query->select('r.id, r.allow, r.return');
			$query->from('#__access_rules AS r');

			// Join on the action-to-rule map
			$query->join('LEFT', '#__access_action_rule_map arm ON arm.rule_id = r.id');

			// Join on the map to get to the action.  This is for WHERE a.name = $actionName later on.
			$query->join('LEFT', '#__access_actions a ON a.id = arm.action_id');

			// Join any individual users mapped to the rule
			$query->join('LEFT', '#__user_rule_map urm ON urm.rule_id = r.id');

			// Join any individual assets mapped to the rule
			$query->join('LEFT', '#__access_asset_rule_map asrm ON asrm.rule_id=r.id');

			// Get all groups that the user is mapped to
			$userGroupIds = $this->getUserGroupMap($userId, true);

			// Note: this should not change during the session of the user
			// unless the admin changes this persons access while they are logged in
			// which is possible when the site uses long session times

			if (!empty($userGroupIds))
			{
				// If the user is in a group, then join on the user-to-group maps
				$sqlUserGroupIds = implode(',', $userGroupIds);
				$query->join('LEFT', '#__usergroup_rule_map ugrm ON ugrm.rule_id = r.id');
				$query->join('LEFT', '#__usergroups ug ON ug.id = ugrm.group_id');
			}

			// Find all the groups that the asset is mapped to
			if ($assetId)
			{
				$assetGroupIds = $this->getAssetGroupMap($assetId, false);

				if (!empty($assetGroupIds)) {
					$sqlAssetGroupIds = implode(',', $assetGroupIds);
				}
			}

			// this join is necessary to weed out rules associated with asset groups
			$query->join('LEFT', '#__access_assetgroup_rule_map agrm ON agrm.rule_id = r.id');

			if ($sqlAssetGroupIds) {
				$query->join('LEFT', '#__access_assetgroups ag ON ag.id = agrm.group_id');
			}

			// The rule must be enabled
			$query->where('r.enabled = 1');

			// Must match on the name of the desired action
			$query->where('a.name='. $db->quote($actionName));

			// Must match either specifically on the User Id or the group the user is in
			$temp = '(urm.user_id = '.(int) $userId.')';
			if ($sqlUserGroupIds) {
				$temp .= ' OR ug.id IN ('. $sqlUserGroupIds .')';
			}
			$query->where('('.$temp.')');

			if (empty($assetId))
			{
				// If the asset is not defined then match nulls
				// @todo It could be possible to improve this to dispense with the Acl Types in future
				$temp = '(asrm.asset_id IS NULL)';
				$query->order('(CASE WHEN urm.user_id IS NULL THEN 0 ELSE 1 END) DESC');
				//TODO Fix this one! order commented out since it not always applies.
				//$query->order('(ug.right_id - ug.left_id) ASC');
			}
			else {
				// Match specifically on the asset supplied
				$temp = '(asrm.asset_id = '. (int) $assetId .')';
			}

			if ($sqlAssetGroupIds)
			{
				// Or match on the asset group id if the asset is in a group
				$temp .= ' OR ag.id IN ('. $sqlAssetGroupIds .')';

				$query->order('(CASE WHEN asrm.asset_id IS NULL THEN 0 ELSE 1 END) DESC');
				$query->order('(ag.right_id - ag.left_id) ASC');
			}
			else {
				$temp .= ' AND agrm.group_id IS NULL';
			}
			$query->where('('.$temp.')');

			// The ordering is always very tricky and makes all the difference in the world.
			// Order (urm.value IS NOT NULL) DESC should put ACLs given to specific AROs
			// ahead of any ACLs given to groups. This works well for exceptions to groups.

			$query->order('r.updated_date DESC');

			// We are only interested in the first row
			// @todo Maybe add a parameter to return all rows for diagnositic purposes
			$db->setQuery($query->toString(), 0, 1);

			$this->_quiet or $this->_log($db->getQuery());

			$row = $db->loadRow();

			// Return Rule Id. This is the key to "hooking" extras like pricing assigned to rules etc... Very useful.
			if (is_array($row)) {
				// Permission granted?
				$allow = (isset($row[1]) && $row[1] == 1);

				$cache[$cacheId] = array('rule_id' => $row[0], 'return_value' => $row[2], 'allow' => $allow);
			}
			else {
				// Permission denied.
				$cache[$cacheId] = array('rule_id' => NULL, 'return_value' => NULL, 'allow' => FALSE);
			}
		}

		$this->_quiet or $this->_log(print_r($cache[$cacheId], 1));

		return $cache[$cacheId]['allow'];
	}

	/**
	 * Returns an array of the Group Ids that a user is mapped to
	 *
	 * @param	int $userId			The User Id
	 * @param	boolean $recursive	Recursively include all child groups (optional)
	 *
	 * @return	array
	 */
	public function getUserGroupMap($userId, $recursive = false)
	{
		// Get a database object.
		$db	= &JFactory::getDbo();

		// First find the usergroups that this user is in
		$query = new JQuery;
		$query->select($recursive ? 'ug2.id' : 'ug1.id');
		$query->from('#__user_usergroup_map AS uugm');
		$query->where('uugm.user_id = '.(int) $userId);
		$query->join('LEFT', '#__usergroups AS ug1 ON ug1.id = uugm.group_id');
		if ($recursive) {
			$query->join('LEFT', '#__usergroups AS ug2 ON ug2.left_id <= ug1.left_id AND ug2.right_id >= ug1.right_id');
		}
		$db->setQuery($query->toString());

		$this->_quiet or $this->_log($db->getQuery());

		$result = $db->loadResultArray();

		// Clean up any NULL values, just in case
		JArrayHelper::toInteger($result);
		array_unshift($result, '1');

		$this->_quiet or $this->_log("User $userId in groups: ".print_r($result, 1));

		return $result;
	}

	/**
	 * Returns an array of the Group Ids that an asset is mapped to
	 *
	 * @param	int $assetId		The Asset Id
	 * @param	boolean $recursive	Recursively include all child groups (optional)
	 *
	 * @return	array
	 */
	public function getAssetGroupMap($assetId, $recursive = false)
	{
		// Get a database object.
		$db	= &JFactory::getDbo();

		// First find the usergroups that this user is in
		$query = new JQuery;
		$query->select($recursive ? 'ag2.id' : 'ag1.id');
		$query->from('#__access_asset_assetgroup_map AS aagm');
		$query->where('aagm.asset_id = '.(int) $assetId);
		$query->join('LEFT', '#__access_assetgroups AS ag1 ON ag1.id = aagm.group_id');
		if ($recursive) {
			$query->join('LEFT', '#__access_assetgroups AS ag2 ON ag2.left_id <= ag1.left_id AND ag2.right_id >= ag1.right_id');
		}
		$db->setQuery($query->toString());

		$this->_quiet or $this->_log($db->getQuery());

		$result = $db->loadResultArray();

		$this->_quiet or $this->_log("Asset $assetId in groups: ".print_r($result, 1));

		return $result;
	}

	/**
	 * Method to get the authorized access levels for a user.
	 *
	 * @access	public
	 * @param	integer	User id.
	 * @param	string	Action name.
	 * @return	array	Array of access level ids.
	 * @since	1.0
	 */
	public function getAuthorisedAccessLevels($userId, $action = 'core.view')
	{
		$inGroupIds = $this->getUserGroupMap($userId, true);

		// Get a database object.
		$db	= &JFactory::getDbo();

		// Build the base query.
		$query	= new JQuery;
		$query->select('DISTINCT ag.id');
		$query->from('`#__access_actions` AS a');
		// Map actions to rules
		$query->join('INNER',	'`#__access_action_rule_map` AS arm ON arm.action_id = a.id');
		$query->join('INNER',	'`#__access_rules` AS r ON r.id = arm.rule_id');
		// Map users and/or user groups to rules
		$query->join('LEFT',	'`#__user_rule_map` AS urm ON r.id = urm.rule_id');
		// Map users to user groups
		//$query->join('LEFT',	'`#__usergroups` AS ug ON ugrm.group_id = ug.id');
		//$query->join('LEFT',	'`#__user_usergroup_map` AS uugm ON ug.id = uugm.group_id');
		// Map the assets to rules
		$query->join('INNER',	'`#__access_assetgroup_rule_map` AS agrm ON r.id = agrm.rule_id');
		$query->join('INNER',	'`#__access_assetgroups` AS ag ON agrm.group_id = ag.id');

		$query->where('a.name = '.$db->quote($action));
		//$query->where('(urm.user_id = '.(int) $userId.' OR uugm.user_id = '.(int) $userId.')');
		if (empty($inGroupIds)) {
			// User is not mapped to any groups
			$query->where('(urm.user_id = '.(int) $userId.')');

		}
		else {
			// User is mapped to one or more groups
			$query->join('LEFT',	'`#__usergroup_rule_map` AS ugrm ON r.id = ugrm.rule_id');
			$query->where('(urm.user_id = '.(int) $userId.' OR ugrm.group_id IN ('.implode(',', $inGroupIds).'))');
		}

		$query->where('r.enabled = 1');
		$query->where('r.allow = 1');

		$db->setQuery($query->toString());

		$this->_quiet or $this->_log($db->getQuery());

		$ids = $db->loadResultArray();
		$ids = array_unique($ids);

		$this->_quiet or $this->_log(print_r($ids, 1));

		return $ids;
	}

	/**
	 * Method to get the available permissions of a given type for a section.
	 *
	 * @access	public
	 * @param	string	Access section name.
	 * @param	integer	Permission type.
	 * @return	array	List of available permissions.
	 * @since	1.0
	 */
	public function getAvailablePermissions($section, $type = JPERMISSION_ACTION)
	{
		// Get a database object.
		$db = &JFactory::getDbo();

		// Check to see if the section exists.
		$db->setQuery(
			'SELECT `id`' .
			' FROM `#__access_sections`' .
			' WHERE `name` = '.$db->Quote($section)
		);
		$sectionId = $db->loadResult();

		// Check for a database error.
		if ($db->getErrorNum()) {
			return new JException($db->getErrorMsg());
		}

		// If the section does not exist, throw an exception.
		if (empty($sectionId)) {
			return new JException(JText::_('Access_Section_Invalid'));
		}

		// Check to see if the action already exists.
		$db->setQuery(
			'SELECT `id`, `name`, `title`, `description`' .
			' FROM `#__access_actions`' .
			' WHERE `section_id` = '.(int) $sectionId .
			' AND `access_type` = '.(int) $type
		);
		$permissions = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			return new JException($db->getErrorMsg());
		}

		return $permissions;
	}

	/**
	 * Returns an array of the User Group ID's that can perform a given action
	 *
	 * @value	string $action	The name of the action
	 *
	 * @return	array
	 */
	function getAuthorisedUsergroups($action, $recursive = false)
	{
		// Get a database object.
		$db	= &JFactory::getDbo();

		// Build the base query.
		$query	= new JQuery;
		$query->select('DISTINCT ug2.id');
		$query->from('`#__access_actions` AS a');
		// Map actions to rules
		$query->join('INNER',	'`#__access_action_rule_map` AS arm ON arm.action_id = a.id');
		$query->join('INNER',	'`#__access_rules` AS r ON r.id = arm.rule_id');
		// Map users and/or user groups to rules
		$query->join('INNER',	'`#__usergroup_rule_map` AS ugrm ON ugrm.rule_id = r.id');

		if ($recursive) {
			$query->join('INNER', '#__usergroups AS ug1 ON ug1.id = ugrm.group_id');
			$query->join('LEFT', '#__usergroups AS ug2 ON ug2.left_id >= ug1.left_id AND ug2.right_id <= ug1.right_id');
		}
		else {
			$query->join('INNER', '#__usergroups AS ug2 ON ug2.id = ugrm.group_id');
		}

		$query->where('r.enabled = 1');
		$query->where('r.allow = 1');

		// Handle an array of actions or just a single action.
		if (is_array($action))
		{
			// Quote the actions.
			foreach ($action as $k => $v) {
				$action[$k] = $db->Quote($v);
			}
			$query->where('(a.name = '.implode(' OR a.name = ', $action).')');
		}
		else {
			$query->where('a.name = '.$db->Quote($action));
		}

		$db->setQuery($query->toString());

		$this->_quiet or $this->_log($db->getQuery());

		$ids = $db->loadResultArray();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseNotice(500, $db->getErrorMsg());
			return false;
		}

		$this->_quiet or $this->_log(print_r($ids, 1));

		return $ids;
	}


	function _log($text)
	{
		echo nl2br($text).'<hr />';
	}
}
