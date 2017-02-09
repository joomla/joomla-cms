<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Authorize
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Class that handles authorisation in a backward (J3.x) compatible way
 *
 * @since  4.0.
 * @deprecated No replacement, to be removed in 4.2
 */
class JAuthorizeImplementationJoomlalegacy extends JAuthorizeImplementationJoomla implements JAuthorizeInterface
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
	protected static $permCache = array();


	/**
	 * Rules object
	 *
	 * @var    object JAccessRules
	 * @since  4.0
	 */
	private $rules = null;

	const IMPLEMENTATION = 'JoomlaLegacy';

	const APPENDSUPPORT = false;

	/**
	 * Instantiate the access class
	 *
	 * @param   mixed            $assetId Assets id, can be integer id or string name or array of string/integer values
	 * @param   JDatabaseDriver  $db       Database object
	 * @param   JAccessRules     $rules    Rules object
	 *
	 *
	 * @since  4.0
	 */

	public function __construct($assetId = 1, JDatabaseDriver $db = null, JAccessRules $rules = null )
	{
		$this->assetId = $assetId;
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
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'rules':
				if ($value instanceof JAccessRules)
				{
					$this->rules = $value;
				}
			break;

			default:
				JAuthorizeImplementationJoomla::__set($name, $value);
		}

		return $this;
	}

	/**
	 * Method for clearing static caches.
	 *
	 * @return  void
	 *
	 * @since  4.0.
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
	 * Check if a user is authorised to perform an action, optionally on an asset.
	 *
	 * @param   integer  $actor    Id of the user/group for which to check authorisation.
	 * @param   mixed    $target  Integer asset id or the name of the asset as a string or array with this values.  Defaults to the global asset node.
	 * @param   string   $action  The name of the action to authorise.
	 * @param   string   $actorType   Optional type of actor. User or group.
	 *
	 * @return  boolean  True if authorised.
	 *
	 * @since   4.0
	 */
	public function check($actor, $target, $action, $actorType = null)
	{
		// Sanitise inputs.
		$id = (int) $actor;

		if ($actorType === null || $actorType == 'group')
		{
			$identities = JUserHelper::getGroupPath($id);
		}
		else
		{
			// Get all groups against which the user is mapped.
			$identities = JUserHelper::getGroupsByUser($id);
			array_unshift($identities, $id * -1);
		}

		$action = $this->cleanAction($action);

		// Clean and filter
		if (isset($target))
		{
			$this->assetId = $target;
		}

		// Default to the root asset node.
		if (empty($this->assetId))
		{
			$assets = JTable::getInstance('Asset', 'JTable', array('dbo' => $this->db));
			$this->assetId = $assets->getRootId();
		}

		// Get the rules for the asset recursively to root if not already retrieved.
		if (empty(self::$assetRules[$this->assetId]))
		{
			// Cache ALL rules for this asset
			self::$assetRules[$this->assetId] = $this->getRules(true, null, null);
		}

		return self::$assetRules[$this->assetId]->allow($action, $identities);
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
	 *
	 * @since  4.0.
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
	 * @since  4.0.
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
	 * @since  4.0.
	 *
	 * @return mixed   Db query result - the return value or null if the query failed.
	 */
	public function getAssetPermissions($recursive = false, $groups = array(), $action = null)
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

		$query->select($prefix . '.id, ' . $prefix . '.rules, p.permission, p.value, ' . $this->db->qn('p') . '.' . $this->db->qn('group'));
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

		// Make all assetIds arrays so we can use them in foreach and IN
		$assetIds = (array) $this->assetId;
		$numerics = $strings = array();

		foreach ($assetIds AS $assetId)
		{
			if (is_numeric($assetId))
			{
				$numerics[] = (int) $assetId;
			}
			else
			{
				$strings[] = (string) $assetId;
			}
		}

		$assetwhere = '';

		if (!empty($numerics))
		{
			$assetwhere .= 'a.id IN (' . implode(',', $numerics) . ')';
		}

		if (!empty($strings))
		{
			if (!empty($assetwhere))
			{
				$assetwhere .= ' OR ';
			}

			$assetwhere .= 'a.name IN (' . $this->db->q(implode($this->db->q(','), $numerics)) . ')';
		}

		$query->where($assetwhere);

		$this->db->setQuery($query);
		$result = $this->db->loadObjectList();

		return $result;
	}


	/**
	 * Merge new permissions with old rules from assets table for backwards compatibility
	 *
	 * @param   object  $results  database query result object with permissions and rules
	 *
	 * @since  4.0.
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
}
