<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Access
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.access.rules');

/**
 * Class that handles all access authorization routines.
 *
 * @package		Joomla.Framework
 * @subpackage	User
 * @since		1.6
 */
class JAccess
{
	/**
	 * @since	1.6
	 */
	protected static $viewLevels = array();

	/**
	 * @since	1.6
	 */
	protected static $assetRules = array();

	/**
	 * Method to check if a user is authorised to perform an action, optionally on an asset.
	 *
	 * @param	integer	$userId	Id of the user for which to check authorisation.
	 * @param	string	$action	The name of the action to authorise.
	 * @param	mixed	$asset	Integer asset id or the name of the asset as a string.  Defaults to the global asset node.
	 *
	 * @return	boolean	True if authorised.
	 * @since	1.6
	 */
	public static function check($userId, $action, $asset = null)
	{
		// Sanitize inputs.
		$userId = (int) $userId;

		$action = strtolower(preg_replace('#[\s\-]+#', '.', trim($action)));
		$asset  = strtolower(preg_replace('#[\s\-]+#', '.', trim($asset)));

		// Default to the root asset node.
		if (empty($asset)) {
			$asset = 1;
		}

		// Get the rules for the asset recursively to root if not already retrieved.
		if (empty(self::$assetRules[$asset])) {
			self::$assetRules[$asset] = self::getAssetRules($asset, true);
		}

		// Get all groups against which the user is mapped.
		$identities = self::getGroupsByUser($userId);
		array_unshift($identities, $userId * -1);

		return self::$assetRules[$asset]->allow($action, $identities);
	}

	/**
	 * Method to check if a group is authorised to perform an action, optionally on an asset.
	 *
	 * @param	integer	$groupId	The path to the group for which to check authorisation.
	 * @param	string	$action		The name of the action to authorise.
	 * @param	mixed	$asset		Integer asset id or the name of the asset as a string.  Defaults to the global asset node.
	 *
	 * @return	boolean	True if authorised.
	 * @since	1.6
	 */
	public static function checkGroup($groupId, $action, $asset = null)
	{
		// Sanitize inputs.
		$groupId = (int) $groupId;
		$action = strtolower(preg_replace('#[\s\-]+#', '.', trim($action)));
		$asset  = strtolower(preg_replace('#[\s\-]+#', '.', trim($asset)));

		// Get group path for group
		$groupPath = self::getGroupPath($groupId);

		// Default to the root asset node.
		if (empty($asset)) {
			$asset = 1;
		}

		// Get the rules for the asset recursively to root if not already retrieved.
		if (empty(self::$assetRules[$asset])) {
			self::$assetRules[$asset] = self::getAssetRules($asset, true);
		}

		return self::$assetRules[$asset]->allow($action, $groupPath);
	}

	/**
	 * Gets the parent groups that a leaf group belongs to in it's branch back to the root of the tree
	 * (including the leaf group id).
	 *
	 * @param	mixed	$groupId	An integer or array of integers representing the identities to check.
	 *
	 * @return	mixed	True if allowed, false for an explicit deny, null for an implicit deny.
	 * @since	1.6
	 */
	protected static function getGroupPath($groupId)
	{
		// TODO: Should we load the whole groups table an build a reverse lookup rather than performing dozens of queries.
		// Initialise variables.
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true)
			->select('parent.id')
			->from('#__usergroups AS parent')
			->innerJoin('#__usergroups AS a ON a.lft BETWEEN parent.lft AND parent.rgt')
			->where('a.id='.(int) $groupId)
			->order('parent.lft');
		$db->setQuery($query);
		$groupPath = $db->loadResultArray();

		return $groupPath;
	}

	/**
	 * Method to return the JRules object for an asset.  The returned object can optionally hold
	 * only the rules explicitly set for the asset or the summation of all inherited rules from
	 * parent assets and explicit rules.
	 *
	 * @param	mixed	$asset		Integer asset id or the name of the asset as a string.
	 * @param	boolean	$recursive	True to return the rules object with inherited rules.
	 *
	 * @return	object	JRules object for the asset.
	 * @since	1.6
	 */
	public static function getAssetRules($asset, $recursive = false)
	{
		// Get the database connection object.
		$db = JFactory::getDbo();

		// Build the database query to get the rules for the asset.
		$query	= $db->getQuery(true);
		$query->select($recursive ? 'b.rules' : 'a.rules');
		$query->from('#__assets AS a');
		$query->group($recursive ? 'b.id' : 'a.id');

		// If the asset identifier is numeric assume it is a primary key, else lookup by name.
		if (is_numeric($asset)) {
			// Get the root even if the asset is not found
			$query->where('(a.id = '.(int) $asset.($recursive ? ' OR a.parent_id=0':'').')');
		}
		else {
			// Get the root even if the asset is not found
			$query->where('(a.name = '.$db->quote($asset).($recursive ? ' OR a.parent_id=0':'').')');
		}

		// If we want the rules cascading up to the global asset node we need a self-join.
		if ($recursive) {
			$query->leftJoin('#__assets AS b ON b.lft <= a.lft AND b.rgt >= a.rgt');
			$query->order('b.lft');
		}

		// Execute the query and load the rules from the result.
		$db->setQuery($query);
		$result	= $db->loadResultArray();

		// Instantiate and return the JRules object for the asset rules.
		$rules	= new JRules;
		$rules->mergeCollection($result);

		return $rules;
	}

	/**
	 * Method to return a list of user groups mapped to a user.  The returned list can optionally hold
	 * only the groups explicitly mapped to the user or all groups both explicitly mapped and inherited
	 * by the user.
	 *
	 * @param	integer	$userId		Id of the user for which to get the list of groups.
	 * @param	boolean	$recursive	True to include inherited user groups.
	 *
	 * @return	array	List of user group ids to which the user is mapped.
	 * @since	1.6
	 */
	public static function getGroupsByUser($userId, $recursive = true)
	{
		static $results = array();

		// Get the database connection object.
		$db = JFactory::getDbo();

		// Creates a simple unique string for each parameter combination:
		$storeId = $userId.':'.(int) $recursive;

		if (!isset($results[$storeId])) {
			// Build the database query to get the rules for the asset.
			$query	= $db->getQuery(true);
			$query->select($recursive ? 'b.id' : 'a.id');
			$query->from('#__user_usergroup_map AS map');
			$query->where('map.user_id = '.(int) $userId);
			$query->leftJoin('#__usergroups AS a ON a.id = map.group_id');

			// If we want the rules cascading up to the global asset node we need a self-join.
			if ($recursive) {
				$query->leftJoin('#__usergroups AS b ON b.lft <= a.lft AND b.rgt >= a.rgt');
			}

			// Execute the query and load the rules from the result.
			$db->setQuery($query);
			$result	= $db->loadResultArray();

			// Clean up any NULL or duplicate values, just in case
			JArrayHelper::toInteger($result);

			if (empty($result)) {
				$result = array('1');
			}
			else {
				$result = array_unique($result);
			}

			$results[$storeId] = $result;
		}

		return $results[$storeId];
	}

	/**
	 * Method to return a list of user Ids contained in a Group
	 *
	 * @param	int		$groupId	The group Id
	 * @param	boolean	$recursive	Recursively include all child groups (optional)
	 *
	 * @return	array
	 * @since	1.6
	 * @todo	This method should move somewhere else?
	 */
	public static function getUsersByGroup($groupId, $recursive = false)
	{
		// Get a database object.
		$db	= JFactory::getDbo();

		$test = $recursive ? '>=' : '=';

		// First find the users contained in the group
		$query	= $db->getQuery(true);
		$query->select('DISTINCT(user_id)');
		$query->from('#__usergroups as ug1');
		$query->join('INNER','#__usergroups AS ug2 ON ug2.lft'.$test.'ug1.lft AND ug1.rgt'.$test.'ug2.rgt');
		$query->join('INNER','#__user_usergroup_map AS m ON ug2.id=m.group_id');
		$query->where('ug1.id='.$db->Quote($groupId));

		$db->setQuery($query);

		$result = $db->loadResultArray();

		// Clean up any NULL values, just in case
		JArrayHelper::toInteger($result);

		return $result;
	}

	/**
	 * Method to return a list of view levels for which the user is authorised.
	 *
	 * @param	integer	$userId	Id of the user for which to get the list of authorised view levels.
	 *
	 * @return	array	List of view levels for which the user is authorised.
	 * @since	1.6
	 */
	public static function getAuthorisedViewLevels($userId)
	{
		// Get all groups that the user is mapped to recursively.
		$groups = self::getGroupsByUser($userId);

		// Only load the view levels once.
		if (empty(self::$viewLevels)) {
			// Get a database object.
			$db	= JFactory::getDBO();

			// Build the base query.
			$query	= $db->getQuery(true);
			$query->select('id, rules');
			$query->from('`#__viewlevels`');

			// Set the query for execution.
			$db->setQuery((string) $query);

			// Build the view levels array.
			foreach ($db->loadAssocList() as $level) {
				self::$viewLevels[$level['id']] = (array) json_decode($level['rules']);
			}
		}

		// Initialise the authorised array.
		$authorised = array(1);

		// Find the authorized levels.
		foreach (self::$viewLevels as $level => $rule)
		{
			foreach ($rule as $id)
			{
				if (($id < 0) && (($id * -1) == $userId)) {
					$authorised[] = $level;
					break;
				}
				// Check to see if the group is mapped to the level.
				elseif (($id >= 0) && in_array($id, $groups)) {
					$authorised[] = $level;
					break;
				}
			}
		}

		return $authorised;
	}

	/**
	 * Method to return a list of actions for which permissions can be set given a component and section.
	 *
	 * @param	string	$component	The component from which to retrieve the actions.
	 * @param	string	$section	The name of the section within the component from which to retrieve the actions.
	 *
	 * @return	array	List of actions available for the given component and section.
	 * @since	1.6
	 */
	public static function getActions($component, $section = 'component')
	{
		$actions = array();

		if (is_file(JPATH_ADMINISTRATOR.'/components/'.$component.'/access.xml')) {
			$xml = simplexml_load_file(JPATH_ADMINISTRATOR.'/components/'.$component.'/access.xml');

			foreach ($xml->children() as $child)
			{
				if ($section == (string) $child['name']) {
					foreach ($child->children() as $action) {
						$actions[] = (object) array('name' => (string) $action['name'], 'title' => (string) $action['title'], 'description' => (string) $action['description']);
					}

					break;
				}
			}
		}

		return $actions;
	}
}
