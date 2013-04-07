<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Access
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.utilities.arrayhelper');

/**
 * Class that handles all access authorisation routines.
 *
 * @package     Joomla.Platform
 * @subpackage  Access
 * @since       11.1
 */
class JAccess
{
	/**
	 * Array of view levels
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected static $viewLevels = array();

	/**
	 * Array of rules for the asset
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected static $assetRules = array();

	/**
	 * Array of user groups.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected static $userGroups = array();

	/**
	 * Array of user group paths.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected static $userGroupPaths = array();

	/**
	 * Array of cached groups by user.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected static $groupsByUser = array();

	/**
	 * Method for clearing static caches.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public static function clearStatics()
	{
		self::$viewLevels = array();
		self::$assetRules = array();
		self::$userGroups = array();
		self::$userGroupPaths = array();
		self::$groupsByUser = array();
	}

	/**
	 * Method to check if a user is authorised to perform an action, optionally on an asset.
	 *
	 * @param   integer  $userId  Id of the user for which to check authorisation.
	 * @param   string   $action  The name of the action to authorise.
	 * @param   mixed    $asset   Integer asset id or the name of the asset as a string.  Defaults to the global asset node.
	 *
	 * @return  boolean  True if authorised.
	 *
	 * @since   11.1
	 */
	public static function check($userId, $action, $asset = null)
	{
		// Sanitise inputs.
		$userId = (int) $userId;

		$action = strtolower(preg_replace('#[\s\-]+#', '.', trim($action)));
		$asset = strtolower(preg_replace('#[\s\-]+#', '.', trim($asset)));

		// Default to the root asset node.
		if (empty($asset))
		{
			$db = JFactory::getDbo();
			$assets = JTable::getInstance('Asset', 'JTable', array('dbo' => $db));
			$rootId = $assets->getRootId();
			$asset = $rootId;
		}

		// Get the rules for the asset recursively to root if not already retrieved.
		if (empty(self::$assetRules[$asset]))
		{
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
	 * @param   integer  $groupId  The path to the group for which to check authorisation.
	 * @param   string   $action   The name of the action to authorise.
	 * @param   mixed    $asset    Integer asset id or the name of the asset as a string.  Defaults to the global asset node.
	 *
	 * @return  boolean  True if authorised.
	 *
	 * @since   11.1
	 */
	public static function checkGroup($groupId, $action, $asset = null)
	{
		// Sanitize inputs.
		$groupId = (int) $groupId;
		$action = strtolower(preg_replace('#[\s\-]+#', '.', trim($action)));
		$asset = strtolower(preg_replace('#[\s\-]+#', '.', trim($asset)));

		// Get group path for group
		$groupPath = self::getGroupPath($groupId);

		// Default to the root asset node.
		if (empty($asset))
		{
			$db = JFactory::getDbo();
			$assets = JTable::getInstance('Asset', 'JTable', array('dbo' => $db));
			$rootId = $assets->getRootId();
		}

		// Get the rules for the asset recursively to root if not already retrieved.
		if (empty(self::$assetRules[$asset]))
		{
			self::$assetRules[$asset] = self::getAssetRules($asset, true);
		}

		return self::$assetRules[$asset]->allow($action, $groupPath);
	}

	/**
	 * Gets the parent groups that a leaf group belongs to in its branch back to the root of the tree
	 * (including the leaf group id).
	 *
	 * @param   mixed  $groupId  An integer or array of integers representing the identities to check.
	 *
	 * @return  mixed  True if allowed, false for an explicit deny, null for an implicit deny.
	 *
	 * @since   11.1
	 */
	protected static function getGroupPath($groupId)
	{
		// Preload all groups
		if (empty(self::$userGroups))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('parent.id, parent.lft, parent.rgt')
				->from('#__usergroups AS parent')
				->order('parent.lft');
			$db->setQuery($query);
			self::$userGroups = $db->loadObjectList('id');
		}

		// Make sure groupId is valid
		if (!array_key_exists($groupId, self::$userGroups))
		{
			return array();
		}

		// Get parent groups and leaf group
		if (!isset(self::$userGroupPaths[$groupId]))
		{
			self::$userGroupPaths[$groupId] = array();

			foreach (self::$userGroups as $group)
			{
				if ($group->lft <= self::$userGroups[$groupId]->lft && $group->rgt >= self::$userGroups[$groupId]->rgt)
				{
					self::$userGroupPaths[$groupId][] = $group->id;
				}
			}
		}

		return self::$userGroupPaths[$groupId];
	}

	/**
	 * Method to return the ID for a group name
	 *
	 * @param   string  $groupname   The group name (title)
	 *
	 * @return  integer  the group id (0 if not found)
	 *
	 * @todo This method is generic and should probably be in a helper class
	 */
	public static function getGroupId($groupname)
	{
		// Set up
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// The query
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__usergroups'));
		$query->where($db->quoteName('title') . ' = ' . $db->quote($groupname));

		// Get the results
		$db->setQuery($query, 0, 1);
		return $db->loadResult();
	}

	/**
	 * Method to return the JAccessRules object for an asset.  The returned object can optionally hold
	 * only the rules explicitly set for the asset or the summation of all inherited rules from
	 * parent assets and explicit rules.
	 *
	 * @param   mixed    $asset      Integer asset id or the name of the asset as a string.
	 * @param   boolean  $recursive  True to return the rules object with inherited rules.
	 *
	 * @return  JAccessRules   JAccessRules object for the asset.
	 *
	 * @since   11.1
	 */
	public static function getAssetRules($asset, $recursive = false)
	{
		// Get the database connection object.
		$db = JFactory::getDbo();

		// Build the database query to get the rules for the asset.
		$query = $db->getQuery(true);
		$query->select($recursive ? 'b.rules' : 'a.rules');
		$query->from('#__assets AS a');
		//sqlsrv change
		$query->group($recursive ? 'b.id, b.rules, b.lft' : 'a.id, a.rules, a.lft');

		// If the asset identifier is numeric assume it is a primary key, else lookup by name.
		if (is_numeric($asset))
		{
			$query->where('(a.id = ' . (int) $asset . ($recursive ? ' OR a.parent_id=0' : '') . ')');
		}
		else
		{
			$query->where('(a.name = ' . $db->quote($asset) . ($recursive ? ' OR a.parent_id=0' : '') . ')');
		}

		// If we want the rules cascading up to the global asset node we need a self-join.
		if ($recursive)
		{
			$query->leftJoin('#__assets AS b ON b.lft <= a.lft AND b.rgt >= a.rgt');
			$query->order('b.lft');
		}

		// Execute the query and load the rules from the result.
		$db->setQuery($query);
		$result = $db->loadColumn();

		// Get the root even if the asset is not found and in recursive mode
		if (empty($result))
		{
			$db = JFactory::getDbo();
			$assets = JTable::getInstance('Asset', 'JTable', array('dbo' => $db));
			$rootId = $assets->getRootId();
			$query = $db->getQuery(true);
			$query->select('rules');
			$query->from('#__assets');
			$query->where('id = ' . $db->quote($rootId));
			$db->setQuery($query);
			$result = $db->loadResult();
			$result = array($result);
		}
		// Instantiate and return the JAccessRules object for the asset rules.
		$rules = new JAccessRules;
		$rules->mergeCollection($result);

		return $rules;
	}

	/**
	 * Method to return a list of user groups mapped to a user. The returned list can optionally hold
	 * only the groups explicitly mapped to the user or all groups both explicitly mapped and inherited
	 * by the user.
	 *
	 * @param   integer  $userId     Id of the user for which to get the list of groups.
	 * @param   boolean  $recursive  True to include inherited user groups.
	 *
	 * @return  array    List of user group ids to which the user is mapped.
	 *
	 * @since   11.1
	 */
	public static function getGroupsByUser($userId, $recursive = true)
	{
		// Creates a simple unique string for each parameter combination:
		$storeId = $userId . ':' . (int) $recursive;

		if (!isset(self::$groupsByUser[$storeId]))
		{
			// Guest user (if only the actually assigned group is requested)
			if (empty($userId) && !$recursive)
			{
				$result = array(JComponentHelper::getParams('com_users')->get('guest_usergroup', 1));
			}
			// Registered user and guest if all groups are requested
			else
			{
				$db = JFactory::getDbo();

				// Build the database query to get the rules for the asset.
				$query = $db->getQuery(true);
				$query->select($recursive ? 'b.id' : 'a.id');
				if (empty($userId))
				{
					$query->from('#__usergroups AS a');
					$query->where('a.id = ' . (int) JComponentHelper::getParams('com_users')->get('guest_usergroup', 1));
				}
				else
				{
					$query->from('#__user_usergroup_map AS map');
					$query->where('map.user_id = ' . (int) $userId);
					$query->leftJoin('#__usergroups AS a ON a.id = map.group_id');
				}

				// If we want the rules cascading up to the global asset node we need a self-join.
				if ($recursive)
				{
					$query->leftJoin('#__usergroups AS b ON b.lft <= a.lft AND b.rgt >= a.rgt');
				}

				// Execute the query and load the rules from the result.
				$db->setQuery($query);
				$result = $db->loadColumn();

				// Clean up any NULL or duplicate values, just in case
				JArrayHelper::toInteger($result);

				if (empty($result))
				{
					$result = array('1');
				}
				else
				{
					$result = array_unique($result);
				}
			}

			self::$groupsByUser[$storeId] = $result;
		}

		return self::$groupsByUser[$storeId];
	}

	/**
	 * Method to return a list of user Ids contained in a Group
	 *
	 * @param   integer  $groupId    The group Id
	 * @param   boolean  $recursive  Recursively include all child groups (optional)
	 *
	 * @return  array
	 *
	 * @since   11.1
	 * @todo    This method should move somewhere else
	 */
	public static function getUsersByGroup($groupId, $recursive = false)
	{
		// Get a database object.
		$db = JFactory::getDbo();

		$test = $recursive ? '>=' : '=';

		// First find the users contained in the group
		$query = $db->getQuery(true);
		$query->select('DISTINCT(user_id)');
		$query->from('#__usergroups as ug1');
		$query->join('INNER', '#__usergroups AS ug2 ON ug2.lft' . $test . 'ug1.lft AND ug1.rgt' . $test . 'ug2.rgt');
		$query->join('INNER', '#__user_usergroup_map AS m ON ug2.id=m.group_id');
		$query->where('ug1.id=' . $db->Quote($groupId));

		$db->setQuery($query);

		$result = $db->loadColumn();

		// Clean up any NULL values, just in case
		JArrayHelper::toInteger($result);

		return $result;
	}

	/**
	 * Method to return a list of view levels for which the user is authorised.
	 *
	 * @param   integer  $userId  Id of the user for which to get the list of authorised view levels.
	 *
	 * @return  array    List of view levels for which the user is authorised.
	 *
	 * @since   11.1
	 */
	public static function getAuthorisedViewLevels($userId)
	{
		// Get all groups that the user is mapped to recursively.
		$groups = self::getGroupsByUser($userId);

		// Only load the view levels once.
		if (empty(self::$viewLevels))
		{
			// Get a database object.
			$db = JFactory::getDBO();

			// Build the base query.
			$query = $db->getQuery(true);
			$query->select('id, rules');
			$query->from($query->qn('#__viewlevels'));

			// Set the query for execution.
			$db->setQuery((string) $query);

			// Build the view levels array.
			foreach ($db->loadAssocList() as $level)
			{
				self::$viewLevels[$level['id']] = (array) json_decode($level['rules']);
			}
		}

		// Initialise the authorised array.
		$authorised = array(1);

		// Find the authorised levels.
		foreach (self::$viewLevels as $level => $rule)
		{
			foreach ($rule as $id)
			{
				if (($id < 0) && (($id * -1) == $userId))
				{
					$authorised[] = $level;
					break;
				}
				// Check to see if the group is mapped to the level.
				elseif (($id >= 0) && in_array($id, $groups))
				{
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
	 * @param   string  $component  The component from which to retrieve the actions.
	 * @param   string  $section    The name of the section within the component from which to retrieve the actions.
	 *
	 * @return  array  List of actions available for the given component and section.
	 *
	 * @since   11.1
	 *
	 * @deprecated  12.3  Use JAccess::getActionsFromFile or JAccess::getActionsFromData instead.
	 *
	 * @codeCoverageIgnore
	 * 
	 */
	public static function getActions($component, $section = 'component')
	{
		JLog::add(__METHOD__ . ' is deprecated. Use JAccess::getActionsFromFile or JAcces::getActionsFromData instead.', JLog::WARNING, 'deprecated');
		$actions = self::getActionsFromFile(
			JPATH_ADMINISTRATOR . '/components/' . $component . '/access.xml',
			"/access/section[@name='" . $section . "']/"
		);
		if (empty($actions))
		{
			return array();
		}
		else
		{
			return $actions;
		}
	}

	/**
	 * Method to return a list of actions from a file for which permissions can be set.
	 *
	 * @param   string  $file   The path to the XML file.
	 * @param   string  $xpath  An optional xpath to search for the fields.
	 *
	 * @return  boolean|array   False if case of error or the list of actions available.
	 *
	 * @since   12.1
	 */
	public static function getActionsFromFile($file, $xpath = "/access/section[@name='component']/")
	{
		if (!is_file($file))
		{
			// If unable to find the file return false.
			return false;
		}
		else
		{
			// Else return the actions from the xml.
			return self::getActionsFromData(JFactory::getXML($file, true), $xpath);
		}
	}

	/**
	 * Method to return a list of actions from a string or from an xml for which permissions can be set.
	 *
	 * @param   string|SimpleXMLElement  $data   The XML string or an XML element.
	 * @param   string                   $xpath  An optional xpath to search for the fields.
	 *
	 * @return  boolean|array   False if case of error or the list of actions available.
	 *
	 * @since   12.1
	 */
	public static function getActionsFromData($data, $xpath = "/access/section[@name='component']/")
	{
		// If the data to load isn't already an XML element or string return false.
		if ((!($data instanceof SimpleXMLElement)) && (!is_string($data)))
		{
			return false;
		}

		// Attempt to load the XML if a string.
		if (is_string($data))
		{
			$data = JFactory::getXML($data, false);

			// Make sure the XML loaded correctly.
			if (!$data)
			{
				return false;
			}
		}

		// Initialise the actions array
		$actions = array();

		// Get the elements from the xpath
		$elements = $data->xpath($xpath . 'action[@name][@title][@description]');

		// If there some elements, analyse them
		if (!empty($elements))
		{
			foreach ($elements as $action)
			{
				// Add the action to the actions array
				$actions[] = (object) array(
					'name' => (string) $action['name'],
					'title' => (string) $action['title'],
					'description' => (string) $action['description'],
					'default' => (string) $action['default']
				);
			}
		}

		// Finally return the actions array
		return $actions;
	}


	/**
	 * Replace the default rules for the target component in the root asset
	 *
	 * The access rules for an component reside in an 'access.xml' file.  
	 *
	 * Each component-specific/custom rule can have a defaulat associated with
	 * it by adding a 'default' clause.  The value of the default is a
	 * comma-separated list of default permissions each of which may have one
	 * of the following forms:
	 *
	 *      default="Author"
	 *           --> True for Author group
	 *  
	 *      default="Author:core.edit"
	 *           --> True for Author if Author has 'core.edit' permission for
	 *               the target component
	 * 
	 *      default="Author:core.edit[com_content]"
	 *           --> True for Author if Author has 'core.edit' permission for
	 *               test component ('com_content' here).  If the component
	 *               (in square brackets) is omitted, the check will default
	 *               to the target component (as in the previous example).
	 *  
	 * No defaults are set if the role is not found.   The core rules may not be overriden.
	 *
	 * WARNING: Cannot be called before component initialization (ok in install post-flight)
	 *
	 * @param   string   $component  name of the target component (eg, 'com_banner')
	 *
	 * @return  boolean  success or failure
	 *
	 * @since   12.1
	 */
	public static function installComponentDefaultRules($component)
	{
		jimport('joomla.access.rules');

		$app = JFactory::getApplication();

		$new_rules = new JAccessRules();

		// The component-specific (non-core) action names
		$custom_actions = Array();

		// Get the actions for this component (which contain the defaults)
		$actions = JAccess::getActions($component);
		foreach ($actions as $action)
		{
			// Make a note of any custom (non-core) action 
			if ( strncmp($action->name, 'core.', 5) !== 0 ) {
				$custom_actions[] = $action->name;
			}

			// Process each default
			if ( $action->default ) {
				$rule_name = $action->name;

				// Make sure the rule is not a core rule
				if ( strncmp($rule_name, 'core.', 5) === 0 ) {
					$app->enqueueMessage("WARNING: Cannot override core rule '$rule_name' for component '$component'!");
					continue;
					}

				// Process each comma-separated clause 
				$rule_set = explode(',', $action->default);
				foreach ($rule_set as $raw_rule)
				{
					// parse the rule
					$rule = $raw_rule;
					if (strpos($rule, ':') === false) {
						$role = $rule;
						$perm = '';
					} else {
						$parts = explode(':', $rule);
						$role = $parts[0];
						$perm = $parts[1];
					}

					// Extract the test component, if specified
					$asset = $component;
					if ( strpos($perm, '[') !== false ) {
						$parts = explode('[', $perm);
						$perm = $parts[0];
						$asset = trim($parts[1], '[] ');
					}

					// Verify that the role (group) is valid on this sytem
					$group_id = JAccess::getGroupId($role);

					// Complain about invalid group name (may have been deleted on this site)
					if ($group_id == 0) {
						$app->enqueueMessage("WARNING: Ignoring group '$role' which does not exist on this system!");
						continue;
					}

					// Decide whether to enforce the rule
					if ($perm) {
						$enforce = JAccess::checkGroup($group_id, $perm, $asset);
					} else {
						$enforce = true;
					}
					if (!$enforce) {
						continue;
					}

					// Construct the rule for this action
					$new_rule = new JAccessRules(Array($rule_name => Array($group_id => 1 )));

					// Merge it with the rest of the new rules
					$new_rules->merge($new_rule);
				}
			}
		}

		// Get the root rules
		$root = JTable::getInstance('asset');
		$root->loadByName('root.1');
		$root_rules = new JAccessRules($root->rules);

		// Purge any existing component-specific custom rules from the root rule
		foreach ($custom_actions as $action)
		{
			$root_rules->removeAction($action);
		}

		// Merge the new rules into the root default rules and save it
		$root_rules->merge($new_rules);
		$root->rules = (string)$root_rules;

		return $root->store();
	}

}
