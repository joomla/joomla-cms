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
 * Class that handles default Joomla authorisation routines.
 *
 * @since  4.0.
 */
class JAuthorizeImplementationJoomla extends JAuthorizeImplementation implements JAuthorizeInterface
{

	/**
	 * Root asset permissions
	 *
	 * @var    array
	 * @since  4.0
	 */
	protected static $rootAsset = null;

	/**
	 * Integer asset id or the name of the asset as a string or array with this values.
	 * _ suffixed to force usage of setters, use property name without_ to set the value
	 *
	 * @var    mixed string or integer or array
	 * @since  4.0
	 */
	protected $assetId_ = 1;

	/**
	 * @const  boolean is append query supported?
	 * @since  4.0
	 */
	const APPENDSUPPORT = true;

	/**
	 * If number of ids passed in one call surpasses this limit,
	 * all permissions will be loaded as query runs much faster this way
	 *
	 * @var    integer
	 * @since  4.0
	 */
	protected $optimizeLimit = 100;

	/**
	 * Instantiate the access class
	 *
	 * @param   mixed            $assetId  Assets id, can be integer or string or array of string/integer values
	 * @param   JAccessRules     $rules    Rules object
	 * @param   JDatabaseDriver  $db       Database object
	 *
	 *
	 * @since  4.0
	 */

	public function __construct($assetId = 1, JDatabaseDriver $db = null)
	{
		$this->assetId = $assetId;
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
			case 'assetId':
				if (is_numeric($value))
				{
					$this->assetId_ = (int) JAuthorizeHelper::cleanAssetId($value);
				}
				elseif (is_array($value))
				{
					$this->assetId_ = array();

					foreach ($value AS $val)
					{
						$this->assetId_[] = is_numeric($val) ? (int) JAuthorizeHelper::cleanAssetId($val) : (string) JAuthorizeHelper::cleanAssetId($val);
					}
				}
				else
				{
					$this->assetId_ = (string) JAuthorizeHelper::cleanAssetId($value);
				}
				break;

			case 'rootAsset':
				static::$rootAsset = $value;
				break;

			default:
				parent::__set($name, $value);
				break;
		}

		return $this;
	}

	/**
	 * Method to get the value
	 *
	 * @param   string  $key           Key to search for in the data array
	 * @param   mixed   $defaultValue  Default value to return if the key is not set
	 *
	 * @return  mixed   Value | null if doesn't exist
	 *
	 * @since   4.0
	 */
	public function __get($key)
	{
		switch ($key)
		{
			case 'assetId':
				return $this->assetId_;
				break;

			default:
				return JAuthorizeImplementation::__get($key);
				break;
		}

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
		$this->__set('authorizationMatrix', null);

		self::$rootAsset = null;
	}

	/**
	 * Check if a user is authorised to perform an action, optionally on an asset.
	 *
	 * @param   integer  $actor       Id of the user/group for which to check authorisation.
	 * @param   mixed    $target      Integer asset id or the name of the asset as a string or array with this values.
	 *                                Defaults to the global asset node.
	 * @param   string   $action      The name of the action to authorise.
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
			$identities = JUserHelper::getGroupPath($id);
		}
		else
		{
			// Get all groups against which the user is mapped.
			$identities = JUserHelper::getGroupsByUser($id);
			array_unshift($identities, $id * -1);
		}

		$action = JAuthorizeHelper::cleanAction($action);

		// Clean and filter - run trough setter
		$this->assetId = $target;

		// Copy value as empty does not fire getter
		$target = $this->assetId;

		// Default to the root asset node.
		if (empty($target))
		{
			$assets = JTable::getInstance('Asset', 'JTable', array('dbo' => $this->db));
			$target = $this->assetId = $assets->getRootId();
		}

		// Local copy as empty/isset doesn't play nicely with getters
		$authorizationMatrix = $this->authorizationMatrix;

		if (is_array($target))
		{
			// Load only ids that don't already exist in matrix
			$newAssetIds = array();

			foreach ($target AS $assetId)
			{
				if (!isset($authorizationMatrix[$assetId][$action]))
				{
					$newAssetIds[] = $assetId;
				}
			}

			$this->assetId = $newAssetIds;

			$this->loadPermissions(true, array(), $action);

			// Revert ids after loading
			$this->assetId = $target;

			$result = array();

			foreach ($target AS $assetId)
			{
				$result[$assetId] = $this->calculate($assetId, $action, $identities);
			}

			return $result;

		}

		elseif (!isset($authorizationMatrix[$target][$action]))
		{
			$this->loadPermissions(true, array(), $action);
		}

		return $this->calculate($target, $action, $identities);
	}

	/**
	 * Load permissions into authorization matrix
	 *
	 * @param   boolean  $recursive  True to return the rules object with inherited rules.
	 * @param   array    $groups     Array of group ids to get permissions for
	 * @param   string   $action     Action name to limit results
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function loadPermissions($recursive = false, $groups = array(), $action = null )
	{
		$result = $this->getAssetPermissions($recursive, $groups, $action);

		// If there is no result get all permisions for root node and cache it!
		if (empty($result))
		{
			if (!isset(self::$rootAsset))
			{
				$this->getRootAssetPermissions();
			}

			$result = self::$rootAsset;
		}

		$this->prefillMatrix($result);
	}

	/**
	 *
	 *
	 * @param   mixed   $asset   Integer asset id or the name of the asset as a string or array with this values.  Defaults to the global asset node.
	 * @param   string  $action The name of the action to authorise.
	 * @param   array   $identities user or group ids
	 *
	 * @return  boolean true if authorized
	 *
	 * @since   4.0
	 */
	protected function calculate($asset, $action, $identities)
	{
		// Implicit deny by default.
		$result = null;

		// isset & empty don't work with getters
		$authorizationMatrix = $this->authorizationMatrix;

		// Check that the inputs are valid.
		if (!empty($identities))
		{
			if (!is_array($identities))
			{
				$identities = array($identities);
			}

			if (isset($authorizationMatrix[$asset][$action]))
			{
				foreach ($identities as $identity)
				{
					// Technically the identity just needs to be unique.
					$identity = (int) $identity;

					// Check if the identity is known.
					if (isset($authorizationMatrix[$asset][$action][$identity]))
					{
						$result = (boolean) $authorizationMatrix[$asset][$action][$identity];

						// An explicit deny wins.
						if ($result === false)
						{
							break;
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Execute query to get permissions from database
	 *
	 * @param   boolean  $recursive  True to return the rules object with inherited rules.
	 * @param   array    $groups     Array of group ids to get permissions for
	 * @param   string   $action     Action name to limit results
	 *
	 * @return mixed   Db query result - the return value or null if the query failed.
	 *
	 * @since   4.0
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
			$straightJoin . 'DISTINCT ' . $prefix . '.id,' . $prefix . '.name, p.permission, p.value, '
			. $this->db->qn('p') . '.' . $this->db->qn('group')
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
	 * Build group part of the query for getAssetPermissions
	 *
	 * @param   array    $groups     Array of group ids to get permissions for
	 *
	 * @return mixed   Db query result - the return value or null if the query failed.
	 *                 	 *
	 * @since   4.0
	 */
	protected function assetGroupQuery($groups)
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

		return $groupQuery;
	}


	/**
	 * Build where part of the query for getAssetPermissions
	 *
	 * @return mixed   Db query result - the return value or null if the query failed.
	 *                 	 *
	 * @since   4.0
	 */
	protected function assetWhere()
	{
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
				$strings[] = $this->db->q((string) $assetId);
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

			$assetwhere .= 'a.name IN (' . implode(',', $strings) . ')';
		}

		return $assetwhere;
	}

	/**
	 * Query root asset permissions
	 *
	 * @return mixed   Db query result - the return value or null if the query failed.
	 *
	 * @since   4.0
	 */
	public function getRootAssetPermissions()
	{
		$query = $this->db->getQuery(true);
		$query  ->select('b.id, b.name, p.permission, p.value, ' . $this->db->qn('p') . '.' . $this->db->qn('group'))
				->from($this->db->qn('#__assets', 'b'))
				->leftJoin($this->db->qn('#__permissions', 'p') . ' ON b.id = p.assetid')
				->where('b.parent_id=0');
		$this->db->setQuery($query);

		self::$rootAsset  = $this->db->loadObjectList();

		return self::$rootAsset;
	}

	/**
	 * Prefill authorizatoon matryx with results form query
	 *
	 * @param   object  $results  database query result object with permissions
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	private function prefillMatrix($results)
	{
		$authorizationMatrix = array();

		foreach ($results AS $result)
		{
			if (isset($result->permission) && !empty($result->permission))
			{
				if (!isset($authorizationMatrix[$result->id]))
				{
					$authorizationMatrix[$result->id] = array();
				}

				if (!isset($authorizationMatrix[$result->id][$result->permission]))
				{
					$authorizationMatrix[$result->id][$result->permission] = array();
				}

				$authorizationMatrix[$result->id][$result->permission][$result->group] = (int) $result->value;
				$authorizationMatrix[$result->name][$result->permission][$result->group] = (int) $result->value;
			}
		}

		$this->authorizationMatrix = $authorizationMatrix;
	}

	/** Inject permissions filter in database object
	 *
	 * @TODO make filter usable by passing asset name
	 * @return  object database query object
	 *                 	 *
	 * @since   4.0
	 */
	public function appendFilterQuery(&$query, $joincolumn, $action, $orWhere = null, $groups = null)
	{
		if (!isset($groups))
		{
			$user   = JFactory::getUser();
			$groups = $user->getAuthorisedGroups();
		}

		$query->select('ass.id AS assid, bs.id AS bssid, bs.rules, p.permission, p.value, p.group');
		$query->leftJoin('#__assets AS ass ON ass.id = ' . $joincolumn);

		// If we want the rules cascading up to the global asset node we need a self-join.
		$query->innerJoin('#__assets AS bs');
		$query->where('ass.lft BETWEEN bs.lft AND bs.rgt');

		// Join permissions table
		$conditions = 'ON bs.id = p.assetid ';

		if (isset($groups))
		{
			$conditions .= $this->assetGroupQuery($groups);
		}

		$conditions .= ' AND p.permission = ' . $this->db->quote($action) . ' ';
		$query->leftJoin('#__permissions AS p ' . $conditions);

		// Magic
		$basicwhere = 'p.permission = ' . $this->db->quote($action) . ' AND p.value=1';

		if (isset($orWhere))
		{
			$basicwhere = '(' . $basicwhere . ' OR ' . $orWhere . ')';
		}

		$query->where($basicwhere);

		$query->where('bs.level = (SELECT max(fs.level) FROM #__assets AS fs
  							LEFT JOIN #__permissions AS pr
 							ON fs.id = pr.assetid 
 						 	WHERE (ass.lft BETWEEN fs.lft AND fs.rgt) AND pr.permission = ' . $this->db->quote($action) . ')');

		return $query;
	}
}
