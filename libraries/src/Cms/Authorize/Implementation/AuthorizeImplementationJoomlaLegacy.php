<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Authorize\Implementation;

use Joomla\Cms\Authorize\AuthorizeInterface;
use Joomla\Cms\Authorize\AuthorizeHelper;
use Joomla\Cms\Table\Table;
use Joomla\Cms\Access\Rules;


defined('JPATH_PLATFORM') or die;

/**
 * Class that handles authorisation in a backward (J3.x) compatible way
 *
 * @since  4.0.
 * @deprecated No replacement, to be removed in 4.2
 */
class AuthorizeImplementationJoomlalegacy extends AuthorizeImplementationJoomla implements AuthorizeInterface
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
	 * @const  boolean is append query supported?
	 * @since  4.0
	 */
	const APPENDSUPPORT = false;


	/**
	 * Instantiate the access class
	 *
	 * @param   mixed            $assetId Assets id, can be integer id or string name or array of string/integer values
	 * @param   \JDatabaseDriver  $db      Database object
	 *
	 *
	 * @since  4.0
	 */
	public function __construct($assetId = 1, \JDatabaseDriver $db = null)
	{
		$this->assetId = $assetId;
		$this->db = isset($db) ? $db : \JFactory::getDbo();
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
			default:
				AuthorizeImplementationJoomla::__set($name, $value);
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
	public function clearStatics()
	{
		self::$assetRules = array();
		self::$permCache = array();
		self::$rootAsset = null;

		$this->authorizationMatrix = null;

		// Legacy
		\JUserHelper::clearStatics();
	}

	/**
	 * Check if a user is authorised to perform an action, optionally on an asset.
	 *
	 * @param   integer  $actor    Id of the user/group for which to check authorisation.
	 * @param   mixed    $target  Integer asset id or the name of the asset as a string or array with this values.  Defaults to the global asset node.
	 * @param   string   $action  The name of the action to authorise.
	 * @param   string   $actorType   Type of actor. User or group.
	 *
	 * @return  mixed  True if authorised and assetId is numeric/named. An array of boolean values if assetId is array.
	 *
	 * @since   4.0
	 */
	public function check($actor, $target, $action, $actorType)
	{
		// Sanitise inputs.
		$id = (int) $actor;

		if ($actorType == 'group')
		{
			$identities = \JUserHelper::getGroupPath($id);
		}
		else
		{
			// Get all groups against which the user is mapped.
			$identities = \JUserHelper::getGroupsByUser($id);
			array_unshift($identities, $id * -1);
		}

		$action = AuthorizeHelper::cleanAction($action);

		// Clean and filter - run trough setter
		$this->assetId = $target;

		// Copy value as empty does not fire getter
		$target = $this->assetId;

		// Default to the root asset node.
		if (empty($target))
		{
			$assets = Table::getInstance('Asset', 'Table', array('dbo' => $this->db));
			$this->assetId = $assets->getRootId();
		}

		$target = (array) $target;

		if (is_array($this->assetId_))
		{
			$result = array();
			$rules = $this->getRules(true, null, $action);

			foreach ($this->assetId_ AS $assetId)
			{
				$result[$assetId] = $rules->allow($action, $identities);
			}

			return $result;
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
	 * @return  Rules   AccessRules object for the asset.
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
		$rules = new Rules();
		$rules->mergeCollection(self::$permCache[$cacheId]);

		// If action was set return only this action's result
		$data = $rules->getData();

		if (isset($action) && isset($data[$action]))
		{
			$data = array($action => $data[$action]);
			$rules = new Rules($data);
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
		/*if ($action == 'core.view')
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
		}*/

		$assetid = $this->assetId;
		static $overLimit = false;

		if ($overLimit || sizeof($this->assetId) > $this->optimizeLimit)
		{
			$assetid = array();
			$overLimit = true;
		}

		$cacheId = md5(serialize(array($assetid, $recursive, $groups, $action)));

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
	private function getAssetPermissions($recursive = false, $groups = array(), $action = null)
	{
		if (sizeof($this->assetId) > $this->optimizeLimit)
		{
			$useIds = false;
			$forceIndex = $straightJoin = '';
		}
		else
		{
			$useIds = true;
			$forceIndex = 'FORCE INDEX FOR JOIN (`lft_rgt_id`)';
			$straightJoin = 'STRAIGHT_JOIN ';
		}

		$query = $this->db->getQuery(true);

		// Build the database query to get the rules for the asset.
		$query->from($this->db->qn('#__assets', 'a'));

		// If we want the rules cascading up to the global asset node we need a self-join.
		if ($recursive)
		{
			$query->join('', $this->db->qn('#__assets', 'b') . $forceIndex . ' ON (a.lft BETWEEN b.lft AND b.rgt) ');

			$prefix = 'b';
		}
		else
		{
			$prefix = 'a';
		}

		$query->select(
					$straightJoin . 'DISTINCT ' . $prefix . '.id,' . $prefix . '.name,' . $prefix
					. '.rules, p.permission, p.value, ' . $this->db->qn('p') . '.' . $this->db->qn('group')
				);

		$conditions = 'ON p.assetid = ' . $prefix . '.id';

		if (isset($groups) && $groups != array())
		{
			$conditions .= $this->assetGroupQuery($groups);
		}

		$query->leftJoin($this->db->qn('#__permissions', 'p') . ' ' . $conditions);

		if (isset($action))
		{
			$query->where('p.permission = ' . $this->db->quote((string) $action));
		}
		else
		{
			$query->where('p.value=1');
		}

		if ($useIds && $recursive)
		{
			$query->where('a.lft > 0 AND b.lft > 0 AND b.rgt > 0');
		}

		if ($useIds)
		{
			$assetwhere = $this->assetWhere();
			$query->where($assetwhere);
		}

		$this->db->setQuery($query);
		$result = $this->db->loadObjectList();

		return $result;
	}


	/**
	 * Query root asset permissions
	 *
	 * @since  4.0.
	 *
	 * @return mixed   Db query result - the return value or null if the query failed.
	 */
	public function getRootAssetPermissions()
	{
		$query = $this->db->getQuery(true);
		$query  ->select('b.id, b.rules, p.permission, p.value, ' . $this->db->qn('p') . '.' . $this->db->qn('group'))
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
