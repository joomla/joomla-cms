<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Access
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Class that handles all access authorisation routines.
 *
 * @since  11.1
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
	 * Array of identities for asset rules
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected static $assetRulesIdentities = array();

	/**
	 * Array of permissions for an asset type
	 * (Array Key = Asset ID)
	 * Also includes the rules string for the asset
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected static $assetPermissionsById = array();

	/**
	 * Array of permissions for an asset type
	 * (Array Key = Asset Name)
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected static $assetPermissionsByName = array();

	/**
	 * Array of the permission parent ID mappings
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected static $assetPermissionsParentIdMapping = array();

	/**
	 * Array of asset types that have been preloaded
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected static $preloadedAssetTypes = array();

	/**
	 * Array of loaded user identities
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected static $identities = array();

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
		self::$assetPermissionsById = array();
		self::$assetPermissionsByName = array();
		self::$assetPermissionsParentIdMapping = array();
		self::$preloadedAssetTypes = array();
		self::$identities = array();
		self::$assetRules = array();
		self::$userGroups = array();
		self::$userGroupPaths = array();
		self::$groupsByUser = array();
	}

	/**
	 * Method to check if a user is authorised to perform an action, optionally on an asset.
	 *
	 * @param   integer  $userId   Id of the user for which to check authorisation.
	 * @param   string   $action   The name of the action to authorise.
	 * @param   mixed    $asset    Integer asset id or the name of the asset as a string.  Defaults to the global asset node.
	 * @param   boolean  $preload  Indicates whether preloading should be used
	 *
	 * @return  boolean|null  True if allowed, false for an explicit deny, null for an implicit deny.
	 *
	 * @since   11.1
	 */
	public static function check($userId, $action, $asset = null, $preload = true)
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
			$asset = $assets->getRootId();
		}

		// Auto preloads assets for the asset type:
		if (!is_numeric($asset) && $preload)
		{
			$assetType = self::getAssetType($asset);

			if (!isset(self::$preloadedAssetTypes[$assetType]))
			{
				self::preload($assetType);
				self::$preloadedAssetTypes[$assetType] = true;
			}
		}

		// Get the rules for the asset recursively to root if not already retrieved.
		if (empty(self::$assetRules[$asset]))
		{
			self::$assetRules[$asset] = self::getAssetRules($asset, true);
		}

		if (!isset(self::$identities[$userId]))
		{
			// Get all groups against which the user is mapped.
			self::$identities[$userId] = self::getGroupsByUser($userId);
			array_unshift(self::$identities[$userId], $userId * -1);
		}

		return self::$assetRules[$asset]->allow($action, self::$identities[$userId]);
	}

	/**
	 * Method to preload the JAccessRules object for the given asset type.
	 *
	 * @param   string|array  $assetTypes  e.g. 'com_content.article'
	 * @param   boolean       $reload      Set to true to reload from database.
	 *
	 * @return   boolean True on success.
	 *
	 * @since    1.6
	 */
	public static function preload($assetTypes = 'components', $reload = false)
	{
		// Get instance of the Profiler:
		$profiler = JProfiler::getInstance('Application');

		// Check for default case:
		$isDefault = (is_string($assetTypes) && in_array($assetTypes, array('components', 'component')));

		// Preload the rules for all of the components:
		if ($isDefault)
		{
			// Mark in the profiler.
			JDEBUG ? $profiler->mark('Start JAccess::preload(components)') : null;

			$components = self::preloadComponents();
			self::$preloadedAssetTypes = array_merge(self::$preloadedAssetTypes, array_flip($components));

			// Mark in the profiler.
			JDEBUG ? $profiler->mark('Finish JAccess::preload(components)') : null;

			// Quick short circuit for default case:
			if ($isDefault)
			{
				return true;
			}
		}

		// If we get to this point, this is a regular asset type
		// and we'll proceed with the preloading process.

		if (!is_array($assetTypes))
		{
			$assetTypes = (array) $assetTypes;
		}

		foreach ($assetTypes as $assetType)
		{
			if (!isset(self::$preloadedAssetTypes[$assetType]) || $reload)
			{
				JDEBUG ? $profiler->mark('New JAccess Preloading Process(' . $assetType . ')') : null;

				self::preloadPermissionsParentIdMapping($assetType);
				JDEBUG ? $profiler->mark('After preloadPermissionsParentIdMapping (' . $assetType . ')') : null;

				self::preloadPermissions($assetType);
				JDEBUG ? $profiler->mark('After preloadPermissions (' . $assetType . ')') : null;

				JDEBUG ? $profiler->mark('End New JAccess Preloading Process(' . $assetType . ')') : null;

				self::$preloadedAssetTypes[$assetType] = true;
			}
		}

		return true;
	}

	/**
	 * Method to recursively retrieve the list of parent Asset IDs
	 * for a particular Asset.
	 *
	 * @param   string      $assetType  e.g. 'com_content.article'
	 * @param   string|int  $assetId    numeric Asset ID
	 *
	 * @return   array  List of Ancestor IDs (includes original $assetId)
	 *
	 * @since    1.6
	 */
	protected static function getAssetAncestors($assetType, $assetId)
	{
		// Get the extension name from the $assetType provided
		$extensionName = self::getExtensionNameFromAsset($assetType);

		// Holds the list of ancestors for the Asset ID:
		$ancestors = array();

		// Add in our starting Asset ID:
		$ancestors[] = (int) $assetId;

		// Initialize the variable we'll use in the loop:
		$id = (int) $assetId;

		while ($id !== 0)
		{
			if (isset(self::$assetPermissionsParentIdMapping[$extensionName][$id]))
			{
				$id = (int) self::$assetPermissionsParentIdMapping[$extensionName][$id]->parent_id;

				if ($id !== 0)
				{
					$ancestors[] = $id;
				}
			}
			else
			{
				// Add additional case to break out of the while loop automatically in
				// the case that the ID is non-existent in our mapping variable above.
				break;
			}
		}

		return $ancestors;
	}

	/**
	 * Method to retrieve the list of Asset IDs and their Parent Asset IDs
	 * and store them for later usage in getAssetRules().
	 *
	 * @param   string  $assetType  e.g. 'com_content.article'
	 *
	 * @return   array  List of Asset IDs (includes Parent Asset ID Info)
	 *
	 * @since    1.6
	 */
	protected static function &preloadPermissionsParentIdMapping($assetType)
	{
		// Get the extension name from the $assetType provided
		$extensionName = self::getExtensionNameFromAsset($assetType);

		if (!isset(self::$assetPermissionsParentIdMapping[$extensionName]))
		{
			// Get the database connection object.
			$db = JFactory::getDbo();

			// Get a fresh query object:
			$query    = $db->getQuery(true);

			// Build the database query:
			$query->select('a.id, a.parent_id');
			$query->from('#__assets AS a');
			$query->where('(a.name LIKE ' . $db->quote($extensionName . '.%') . ' OR a.name = ' . $db->quote($extensionName) . ' OR a.id = 1)');

			// Get the Name Permission Map List
			$db->setQuery($query);
			$parentIdMapping = $db->loadObjectList('id');

			self::$assetPermissionsParentIdMapping[$extensionName] = &$parentIdMapping;
		}

		return self::$assetPermissionsParentIdMapping[$extensionName];
	}

	/**
	 * Method to retrieve the Asset Rule strings for this particular
	 * Asset Type and stores them for later usage in getAssetRules().
	 * Stores 2 arrays: one where the list has the Asset ID as the key
	 * and a second one where the Asset Name is the key.
	 *
	 * @param   string  $assetType  e.g. 'com_content.article'
	 *
	 * @return   bool  True
	 *
	 * @since    1.6
	 */
	protected static function preloadPermissions($assetType)
	{
		// Get the extension name from the $assetType provided
		$extensionName = self::getExtensionNameFromAsset($assetType);

		if (!isset(self::$assetPermissionsById[$extensionName]) && !isset(self::$assetPermissionsByName[$extensionName]))
		{
			// Get the database connection object.
			$db = JFactory::getDbo();

			// Get a fresh query object:
			$query    = $db->getQuery(true);

			// Build the database query:
			$query->select('a.id, a.name, a.rules');
			$query->from('#__assets AS a');
			$query->where('(a.name LIKE ' . $db->quote($extensionName . '.%') . ' OR a.name = ' . $db->quote($extensionName) . ' OR a.id = 1 )');

			// Get the Name Permission Map List
			$db->setQuery($query);

			$iterator = $db->getIterator();

			self::$assetPermissionsById[$extensionName] = array();
			self::$assetPermissionsByName[$extensionName] = array();

			foreach ($iterator as $row)
			{
				self::$assetPermissionsById[$extensionName][$row->id] = $row;
				self::$assetPermissionsByName[$extensionName][$row->name] = $row;
			}
		}

		return true;
	}

	/**
	 * Method to preload the JAccessRules objects for all components.
	 *
	 * Note: This will only get the base permissions for the component.
	 * e.g. it will get 'com_content', but not 'com_content.article.1' or
	 * any more specific asset type rules.
	 *
	 * @return   array Array of component names that were preloaded.
	 *
	 * @since    1.6
	 */
	protected static function preloadComponents()
	{
		// Get the database connection object.
		$db = JFactory::getDbo();

		// Build the database query:
		$query    = $db->getQuery(true);
		$query->select('element');
		$query->from('#__extensions');
		$query->where('type = ' . $db->quote('component'));
		$query->where('enabled = ' . $db->quote(1));

		// Set the query and get the list of active components:
		$db->setQuery($query);
		$components = $db->loadColumn();

		// Get a fresh query object:
		$query    = $db->getQuery(true);

		// Build the in clause for the queries:
		$inClause = '';
		$last = end($components);

		foreach ($components as $component)
		{
			if ($component === $last)
			{
				$inClause .= $db->quote($component);
			}
			else
			{
				$inClause .= $db->quote($component) . ',';
			}
		}

		// Build the database query:
		$query->select('a.name, a.rules');
		$query->from('#__assets AS a');
		$query->where('(a.name IN (' . $inClause . ') OR a.name = ' . $db->quote('root.1') . ')');

		// Get the Name Permission Map List
		$db->setQuery($query);
		$namePermissionMap = $db->loadAssocList('name');

		$root = array();
		$root['rules'] = '';

		if (isset($namePermissionMap['root.1']))
		{
			$root = $namePermissionMap['root.1'];
			unset($namePermissionMap['root.1']);
		}

		// Container for all of the JAccessRules for this $assetType
		$rulesList = array();

		// Collects permissions for each $assetName and adds
		// into the $assetRules class variable.
		foreach ($namePermissionMap as $assetName => &$permissions)
		{
			// Instantiate and return the JAccessRules object for the asset rules.
			$rules    = new JAccessRules;
			$rules->mergeCollection(array($root['rules'], $permissions['rules']));

			$rulesList[$assetName] = $rules;
		}

		unset($assetName);
		unset($permissions);

		// Merge our rules list with self::$assetRules
		self::$assetRules = self::$assetRules + $rulesList;
		unset($rulesList);

		return $components;
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
			$asset = $assets->getRootId();
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
		// Load all the groups to improve performance on intensive groups checks
		$groups = JHelperUsergroups::getInstance()->getAll();

		if (!isset($groups[$groupId]))
		{
			return array();
		}

		return $groups[$groupId]->path;
	}

	/**
	 * Method to return the JAccessRules object for an asset.  The returned object can optionally hold
	 * only the rules explicitly set for the asset or the summation of all inherited rules from
	 * parent assets and explicit rules.
	 *
	 * @param   mixed    $asset                 Integer asset id or the name of the asset as a string.
	 * @param   boolean  $recursive             True to return the rules object with inherited rules.
	 * @param   boolean  $recursiveParentAsset  True to calculate the rule also based on inherited component/extension rules.
	 *
	 * @return  JAccessRules   JAccessRules object for the asset.
	 *
	 * @since   11.1
	 */
	public static function getAssetRules($asset, $recursive = false, $recursiveParentAsset = true)
	{
		// Get instance of the Profiler:
		$profiler = JProfiler::getInstance('Application');

		$extensionName = self::getExtensionNameFromAsset($asset);

		// Almost all calls should have recursive set to true
		// so we'll get to take advantage of preloading:
		if ($recursive && $recursiveParentAsset && isset(self::$assetPermissionsByName[$extensionName])
			&& isset(self::$assetPermissionsByName[$extensionName][$asset]))
		{
			// Mark in the profiler.
			JDEBUG ? $profiler->mark('Start JAccess::getAssetRules New (' . $asset . ')') : null;

			$assetType = self::getAssetType($asset);
			$assetId = self::$assetPermissionsByName[$extensionName][$asset]->id;

			$ancestors = array_reverse(self::getAssetAncestors($assetType, $assetId));

			// Collects permissions for each $asset
			$collected = array();

			foreach ($ancestors as $id)
			{
				$collected[] = self::$assetPermissionsById[$extensionName][$id]->rules;
			}

			/**
			* Hashing the collected rules allows us to store
			* only one instance of the JAccessRules object for
			* Assets that have the same exact permissions...
			* it's a great way to save some memory.
			*/
			$hash = md5(implode(',', $collected));

			if (!isset(self::$assetRulesIdentities[$hash]))
			{
				$rules    = new JAccessRules;
				$rules->mergeCollection($collected);

				self::$assetRulesIdentities[$hash] = $rules;
			}

			// Mark in the profiler.
			JDEBUG ? $profiler->mark('Finish JAccess::getAssetRules New (' . $asset . ')') : null;

			return self::$assetRulesIdentities[$hash];
		}
		else
		{
			// Mark in the profiler.
			JDEBUG ? $profiler->mark('Start JAccess::getAssetRules Old (' . $asset . ')') : null;

			if ($asset === "1")
			{
				// There's no need to process it with the
				// recursive method for the Root Asset ID.
				$recursive = false;
			}

			// Get the database connection object.
			$db = JFactory::getDbo();

			// Build the database query to get the rules for the asset.
			$query = $db->getQuery(true)
				->select($recursive ? 'b.rules' : 'a.rules')
				->from('#__assets AS a');

			$extensionString = '';

			if ($recursiveParentAsset && ($extensionName !== $asset || is_numeric($asset)))
			{
				$extensionString = ' OR a.name = ' . $db->quote($extensionName);
			}

			$recursiveString = '';

			if ($recursive)
			{
				$recursiveString = ' OR a.parent_id=0';
			}

			// If the asset identifier is numeric assume it is a primary key, else lookup by name.
			if (is_numeric($asset))
			{
				$query->where('(a.id = ' . (int) $asset . $extensionString . $recursiveString . ')');
			}
			else
			{
				$query->where('(a.name = ' . $db->quote($asset) . $extensionString . $recursiveString . ')');
			}

			// If we want the rules cascading up to the global asset node we need a self-join.
			if ($recursive)
			{
				$query->join('LEFT', '#__assets AS b ON b.lft <= a.lft AND b.rgt >= a.rgt')
					->order('b.lft');
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
				$query->clear()
					->select('rules')
					->from('#__assets')
					->where('id = ' . $db->quote($rootId));
				$db->setQuery($query);
				$result = $db->loadResult();
				$result = array($result);
			}

			// Instantiate and return the JAccessRules object for the asset rules.
			$rules = new JAccessRules;
			$rules->mergeCollection($result);

			JDEBUG ? $profiler->mark('Finish JAccess::getAssetRules Old (' . $asset . ')') : null;

			return $rules;
		}
	}

	/**
	 * Method to get the extension name from the asset name.
	 *
	 * @param   string  $asset  Asset Name
	 *
	 * @return  string  Extension Name.
	 *
	 * @since    1.6
	 */
	public static function getExtensionNameFromAsset($asset)
	{
		static $loaded = array();

		if (!isset($loaded[$asset]))
		{
			if (is_numeric($asset))
			{
				$table = JTable::getInstance('Asset');
				$table->load($asset);
				$assetName = $table->name;
			}
			else
			{
				$assetName = $asset;
			}

			$firstDot = strpos($assetName, '.');

			if ($assetName !== 'root.1' && $firstDot !== false)
			{
				$assetName = substr($assetName, 0, $firstDot);
			}

			$loaded[$asset] = $assetName;
		}

		return $loaded[$asset];
	}

	/**
	 * Method to get the asset type from the asset name.
	 *
	 * For top level components this returns "components":
	 * 'com_content' returns 'components'
	 *
	 * For other types:
	 * 'com_content.article.1' returns 'com_content.article'
	 * 'com_content.category.1' returns 'com_content.category'
	 *
	 * @param   string  $asset  Asset Name
	 *
	 * @return  string  Asset Type.
	 *
	 * @since    1.6
	 */
	public static function getAssetType($asset)
	{
		$lastDot = strrpos($asset, '.');

		if ($asset !== 'root.1' && $lastDot !== false)
		{
			$assetType = substr($asset, 0, $lastDot);
		}
		else
		{
			$assetType = 'components';
		}

		return $assetType;
	}

	/**
	 * Method to return the title of a user group
	 *
	 * @param   integer  $groupId  Id of the group for which to get the title of.
	 *
	 * @return  string  Tthe title of the group
	 *
	 * @since   3.5
	 */
	public static function getGroupTitle($groupId)
	{
		// Fetch the group title from the database
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('title')
			->from('#__usergroups')
			->where('id = ' . $db->quote($groupId));
		$db->setQuery($query);

		return $db->loadResult();
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
			// TODO: Uncouple this from JComponentHelper and allow for a configuration setting or value injection.
			if (class_exists('JComponentHelper'))
			{
				$guestUsergroup = JComponentHelper::getParams('com_users')->get('guest_usergroup', 1);
			}
			else
			{
				$guestUsergroup = 1;
			}

			// Guest user (if only the actually assigned group is requested)
			if (empty($userId) && !$recursive)
			{
				$result = array($guestUsergroup);
			}
			// Registered user and guest if all groups are requested
			else
			{
				$db = JFactory::getDbo();

				// Build the database query to get the rules for the asset.
				$query = $db->getQuery(true)
					->select($recursive ? 'b.id' : 'a.id');

				if (empty($userId))
				{
					$query->from('#__usergroups AS a')
						->where('a.id = ' . (int) $guestUsergroup);
				}
				else
				{
					$query->from('#__user_usergroup_map AS map')
						->where('map.user_id = ' . (int) $userId)
						->join('LEFT', '#__usergroups AS a ON a.id = map.group_id');
				}

				// If we want the rules cascading up to the global asset node we need a self-join.
				if ($recursive)
				{
					$query->join('LEFT', '#__usergroups AS b ON b.lft <= a.lft AND b.rgt >= a.rgt');
				}

				// Execute the query and load the rules from the result.
				$db->setQuery($query);
				$result = $db->loadColumn();

				// Clean up any NULL or duplicate values, just in case
				$result = ArrayHelper::toInteger($result);

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
		$query = $db->getQuery(true)
			->select('DISTINCT(user_id)')
			->from('#__usergroups as ug1')
			->join('INNER', '#__usergroups AS ug2 ON ug2.lft' . $test . 'ug1.lft AND ug1.rgt' . $test . 'ug2.rgt')
			->join('INNER', '#__user_usergroup_map AS m ON ug2.id=m.group_id')
			->where('ug1.id=' . $db->quote($groupId));

		$db->setQuery($query);

		$result = $db->loadColumn();

		// Clean up any NULL values, just in case
		$result = ArrayHelper::toInteger($result);

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
			$db = JFactory::getDbo();

			// Build the base query.
			$query = $db->getQuery(true)
				->select('id, rules')
				->from($db->quoteName('#__viewlevels'));

			// Set the query for execution.
			$db->setQuery($query);

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
	 * @since       11.1
	 * @deprecated  12.3 (Platform) & 4.0 (CMS)  Use JAccess::getActionsFromFile or JAccess::getActionsFromData instead.
	 * @codeCoverageIgnore
	 */
	public static function getActions($component, $section = 'component')
	{
		JLog::add(__METHOD__ . ' is deprecated. Use JAccess::getActionsFromFile or JAccess::getActionsFromData instead.', JLog::WARNING, 'deprecated');

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
		if (!is_file($file) || !is_readable($file))
		{
			// If unable to find the file return false.
			return false;
		}
		else
		{
			// Else return the actions from the xml.
			$xml = simplexml_load_file($file);

			return self::getActionsFromData($xml, $xpath);
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
			try
			{
				$data = new SimpleXMLElement($data);
			}
			catch (Exception $e)
			{
				return false;
			}

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
				);
			}
		}

		// Finally return the actions array
		return $actions;
	}
}
