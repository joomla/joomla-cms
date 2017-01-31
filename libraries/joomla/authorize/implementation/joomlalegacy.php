<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Access
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Class that handles all access authorisation routines.
 *
 * @since  11.1
 */
class JAuthorizeImplementationJoomlaLegacy extends JAuthorizeImplementation implements JAuthorizeInterface
{

	/**
	 * Array of rules for the asset
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected static $assetRules = array();

	/**
	 * Array of permissions
	 *
	 * @var    array
	 * @since  4.0
	 */
	public static $permCache = array();

	/**
	 * Root asset permissions
	 *
	 * @var    array
	 * @since  4.0
	 */
	public static $rootAsset = null;

	/**
	 * Asset id
	 *
	 * @var    mixed string or integer
	 * @since  4.0
	 */
	protected $assetId = 1;

	/**
	 * Rules object
	 *
	 * @var    object JAccessRules
	 * @since  4.0
	 */
	protected $rules = null;

	/**
	 * Database object
	 *
	 * @var    object JDatabase object
	 * @since  4.0
	 */
	protected $db = null;

	const IMPLEMENTATION = 'JoomlaLegacy';

	/**
	 * Instantiate the access class
	 *
	 * @param   mixed            $assetId  Assets id, can be numeric or string
	 * @param   JAccessRules     $rules    Rules object
	 * @param   JDatabaseDriver  $db       Database object
	 *
	 *
	 * @since  4.0
	 */

	public function __construct($assetId = 1, JAccessRules $rules = null, JDatabaseDriver $db = null)
	{
		$this->set('assetId', $assetId);
		$this->rules = isset($rules) ? $rules : new JAccessRules();
		$this->db = isset($db) ? $db : JFactory::getDbo();
	}

	/**
	 * Method to set a value Example: $access->set('items', $items);
	 *
	 * @param   string  $name   Name of the property
	 * @param   mixed   $value  Value to assign to the property
	 *
	 * @return  self
	 *
	 * @since   4.0
	 */
	public function set($name, $value)
	{
		switch ($name)
		{
			case 'assetId':
				if (is_numeric($value))
				{
					$this->assetId = (int) $value;
				}
				else
				{
					$this->assetId = (string) $value;
				}
				break;
			case 'rules':
				if ($value instanceof JAccessRules)
				{
					$this->rules = $value;
				}
		}

		return $this;
	}

	/**
	 * Method to get the value
	 *
	 * @param   string  $key           Key to search for in the data array
	 * @param   mixed   $defaultValue  Default value to return if the key is not set
	 *
	 * @return  mixed   Value | defaultValue if doesn't exist
	 *
	 * @since   4.0
	 */
	public function get($key, $defaultValue = null)
	{
		return isset($this->$key) ? $this->$key : $defaultValue;
	}

	/**
	 * Method for clearing static caches.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public static function clearStatics()
	{
		self::$assetRules = array();
		self::$permCache = array();
		parent::$authorizationMatrix[self::IMPLEMENTATION] = null;
		self::$rootAsset = null;

		// Legacy
		JUserHelper::clearStatics();
	}

	/**
	 * Method to check if a user is authorised to perform an action, optionally on an asset.
	 *
	 * @param   integer  $id      Id of the user/group for which to check authorisation.
	 * @param   string   $action  The name of the action to authorise.
	 * @param   mixed    $asset   Integer asset id or the name of the asset as a string.  Defaults to the global asset node.
	 * @param   boolean  $group   Is id a group id?
	 *
	 * @return  boolean  True if authorised.
	 *
	 * @since   4.0
	 */
	public function check($id, $action, $asset = null, $group = false)
	{
		// Sanitise inputs.
		$id = (int) $id;

		if ($group)
		{
			$identities = JUserHelper::getGroupPath($id);
		}
		else
		{
			// Get all groups against which the user is mapped.
			$identities = JUserHelper::getGroupsByUser($id);
			array_unshift($identities, $id * -1);
		}

		$action = strtolower(preg_replace('#[\s\-]+#', '.', trim($action)));

		if (isset($asset))
		{
			$this->set('assetId', $asset);
		}

		$asset = strtolower(preg_replace('#[\s\-]+#', '.', trim($this->assetId)));

		// Default to the root asset node.
		if (empty($asset))
		{
			$assets = JTable::getInstance('Asset', 'JTable', array('dbo' => $this->db));
			$asset = $assets->getRootId();
			$this->set('assetId', $asset);
		}

		// Get the rules for the asset recursively to root if not already retrieved.
		if (empty(self::$assetRules[$asset]))
		{
			// Cache ALL rules for this asset
			self::$assetRules[$asset] = $this->getRules(true, null, null);
		}

		return self::$assetRules[$asset]->allow($action, $identities);
	}

	/**
	 * Speed enhanced permission lookup function
	 * Returns JAccessRules object for an asset.  The returned object can optionally hold
	 * only the rules explicitly set for the asset or the summation of all inherited rules from
	 * parent assets and explicit rules.
	 *
	 * @param   boolean  $recursive  True to return the rules object with inherited rules.
	 * @param   array    $groups     Array of group ids to get permissions for
	 * @param   string   $action     Action name to limit results
	 *
	 * @return  JAccessRules   JAccessRules object for the asset.
	 */
	public function getRules($recursive = false, $groups = null, $action = null )
	{
		// Make a copy for later
		$actionForCache = $action;

		$cacheId = $this->getCacheId($recursive, $groups, $actionForCache);

		if (!isset(self::$permCache[$cacheId]))
		{
			$result = $this->getAssetPermissions($recursive, $groups, $actionForCache);

			// If no result get all permisions for root node and cache it!
			if (empty($result))
			{
				if (!isset(self::$rootAsset))
				{
					$this->getRootAssetPermissions();
				}

				$result = self::$rootAsset;
			}

			self::$permCache[$cacheId] = $this->mergePermissionsRules($result);
		}

		// Instantiate and return the JAccessRules object for the asset rules.
		$this->rules->mergeCollection(self::$permCache[$cacheId]);
		$rules = $this->rules;

		// If action was set return only this action's result
		$data = $rules->getData();

		if (isset($action) && isset($data[$action]))
		{
			$data = array($action => $data[$action]);
			$rules = new JAccessRules($data);
		}

		return $rules;
	}

	/**
	 * Calculate internal cache id
	 *
	 * @param   boolean  $recursive  True to return the rules object with inherited rules.
	 * @param   array    $groups     Array of group ids to get permissions for
	 * @param   string   &$action    Action name used for id calculation
	 *
	 * @return string
	 */

	private function getCacheId($recursive, $groups, &$action)
	{
		// We are optimizing only view for frontend, otherwise 1 query for all actions is faster globaly due to cache
		if ($action == 'core.view')
		{
			// If we have all actions query already take data from cache
			if (isset(self::$permCache[md5(serialize(array($this->assetId, $recursive, $groups, null)))]))
			{
				$action = null;
			}
		}
		else
		{
			// Don't use action in cacheId calc and query - faster with multiple actions
			$action = null;
		}

		$cacheId = md5(serialize(array($this->assetId, $recursive, $groups, $action)));

		return $cacheId;
	}

	/**
	 * Query permissions based on asset id.
	 *
	 * @param   boolean  $recursive  True to return the rules object with inherited rules.
	 * @param   array    $groups     Array of group ids to get permissions for
	 * @param   string   $action     Action name to limit results
	 *
	 * @return mixed   Db query result - the return value or null if the query failed.
	 */
	public  function getAssetPermissions($recursive = false, $groups = array(), $action = null)
	{
		$query = $this->db->getQuery(true);

		// Build the database query to get the rules for the asset.
		$query->from($this->db->qn('#__assets', 'a'));

		// If we want the rules cascading up to the global asset node we need a self-join.
		if ($recursive)
		{
			$query->from($this->db->qn('#__assets', 'b'));
			$query->where('a.lft BETWEEN b.lft AND b.rgt');
			$query->order('b.lft');
			$prefix = 'b';
		}
		else
		{
			$prefix = 'a';
		}

		$query->select($prefix . '.id, ' . $prefix . '.rules, p.permission, p.value, ' . $this->db->qn('p'). '.' . $this->db->qn('group'));
		$conditions = 'ON ' . $prefix . '.id = p.assetid ';

		if (isset($groups) && $groups != array())
		{
			if (is_string($groups))
			{
				$groups = array($groups);
			}

			$counter   = 1;
			$allGroups = count($groups);

			$groupQuery = ' AND (';

			foreach ($groups AS $group)
			{
				$groupQuery .= 'p.group = ' . $this->db->quote((string) $group);
				$groupQuery .= ($counter < $allGroups) ? ' OR ' : ' ) ';
				$counter++;
			}

			$conditions .= $groupQuery;
		}

		if (isset($action))
		{
			$conditions .= ' AND p.permission = ' . $this->db->quote((string) $action) . ' ';
		}

		$query->leftJoin($this->db->qn('#__permissions', 'p') . ' ' . $conditions);

		// If the asset identifier is numeric assume it is a primary key, else lookup by name.
		if (is_numeric($this->assetId))
		{
			$query->where('a.id = ' . (int) $this->assetId);
		}
		else
		{
			$query->where('a.name = ' . $this->db->quote((string) $this->assetId));
		}

		$test = (string)$query;

		$this->db->setQuery($query);
		$result = $this->db->loadObjectList();

		return $result;
	}

	/**
	 * Query root asset permissions
	 *
	 * @return mixed   Db query result - the return value or null if the query failed.
	 */
	public function getRootAssetPermissions()
	{
		$query = $this->db->getQuery(true);
		$query  ->select('b.id, b.rules, p.permission, p.value, ' . $this->db->qn('p'). '.' . $this->db->qn('group'))
				->from($this->db->qn('#__assets', 'b'))
				->leftJoin($this->db->qn('#__permissions', 'p') . ' ON b.id = p.assetid')
				->where('b.parent_id=0');
		$this->db->setQuery($query);

		self::$rootAsset  = $this->db->loadObjectList();

		return self::$rootAsset;
	}

	/**
	 * Merge new permissions with old rules from assets table for backwards compatibility
	 *
	 * @param   object  $results  database query result object with permissions and rules
	 *
	 * @return  array   authorisation matrix
	 */
	private function mergePermissionsRules($results)
	{
		$mergedResult = array();

		foreach ($results AS $result)
		{
			if (isset($result->permission) && !empty($result->permission))
			{
				if (!isset($mergedResult[$result->id]))
				{
					$mergedResult[$result->id] = array();
				}

				if (!isset($mergedResult[$result->id][$result->permission]))
				{
					$mergedResult[$result->id][$result->permission] = array();
				}

				$mergedResult[$result->id][$result->permission][$result->group] = (int) $result->value;
			}
			elseif (isset($result->rules) && $result->rules != '{}')
			{
				$mergedResult[$result->id] = json_decode((string) $result->rules, true);
			}
		}

		$mergedResult = array_values($mergedResult);

		return $mergedResult;
	}

	/* Inject permissions filter in database object
	 *
	 * @TODO make filter usable by passing asset name
	 */
	public static function insertFilterQuery(&$query, $joinfield, $permission, $orwhere = null, $groups = null)
	{

		$db = JFactory::getDbo();

		if (!isset($groups))
		{
			$user   = JFactory::getUser();
			$groups = $user->getAuthorisedGroups();
		}

		/*if ($user->isAdmin((int) $user->id) == true)
		{
			return; // No filter for admins
		}*/

		$query->select('ass.id AS assid, bs.id AS bssid, bs.rules, p.permission, p.value, p.group');
		$query->leftJoin('#__assets AS ass ON ass.id = ' . $joinfield);

		// If we want the rules cascading up to the global asset node we need a self-join.
		$query->innerJoin('#__assets AS bs');
		$query->where('ass.lft BETWEEN bs.lft AND bs.rgt');

		// Join permissions table
		$conditions = 'ON bs.id = p.assetid ';

		if (isset($groups))
		{
			if (is_string($groups))
			{
				$groups = array($groups);
			}

			$counter   = 1;
			$allgroups = count($groups);

			$gquery = ' AND (';

			foreach ($groups AS $group)
			{
				$gquery .= 'p.group = ' . $db->quote((string) $group);
				$gquery .= ($counter < $allgroups) ? ' OR ' : ' ) ';
				$counter++;
			}

			$conditions .= $gquery;
		}

		$conditions .= ' AND p.permission = ' . $db->quote($permission) . ' ';
		$query->leftJoin('#__permissions AS p ' . $conditions);

		// Magic
		$basicwhere = 'p.permission = ' . $db->quote($permission) . ' AND p.value=1';

		if (isset($orwhere))
		{
			$basicwhere = '(' . $basicwhere . ' OR ' . $orwhere . ')';
		}

		$query->where($basicwhere);

		$query->where('bs.level = (SELECT max(fs.level) FROM #__assets AS fs
  							LEFT JOIN #__permissions AS pr
 							ON fs.id = pr.assetid 
 						 	WHERE (ass.lft BETWEEN fs.lft AND fs.rgt) AND pr.permission = ' . $db->quote($permission) . ')');

		return $query;
	}
}
