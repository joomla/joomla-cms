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
 * @since  11.1
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

	const IMPLEMENTATION = 'Joomla';

	const APPENDSUPPORT = true;

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
					$this->assetId_ = (int) $this->cleanAssetId($value);
				}
				elseif (is_array($value))
				{
					$this->assetId_ = array();

					foreach ($value AS $val)
					{
						$this->assetId_ = is_numeric($val) ? (int) $this->cleanAssetId($val) : (string) $this->cleanAssetId($val);
					}
				}
				else
				{
					$this->assetId_ = (string) $this->cleanAssetId($value);
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
	 * Method for clearing static caches.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public static function clearStatics()
	{
		parent::$authorizationMatrix[self::IMPLEMENTATION] = null;

		self::$rootAsset = null;
	}

	/**
	 * Method to check if a user is authorised to perform an action, optionally on an asset.
	 *
	 * @param   integer  $id      Id of the user/group for which to check authorisation.
	 * @param   string   $action  The name of the action to authorise.
	 * @param   mixed    $asset   Integer asset id or the name of the asset as a string or array with this values.  Defaults to the global asset node.
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

		$action = $this->cleanAction($action);

		// Clean and filter
		if (isset($asset))
		{
			$this->assetId = $asset;
		}

		// Default to the root asset node.
		if (empty($this->assetId))
		{
			$assets = JTable::getInstance('Asset', 'JTable', array('dbo' => $this->db));
			$this->assetId = $assets->getRootId();
		}

		// Get the rules for the asset recursively to root if not already retrieved.
		if (empty(parent::$authorizationMatrix[self::IMPLEMENTATION][$this->assetId]))
		{
			// Cache ALL permissions for this asset
			$this->loadPermissions(true, null, null);
		}

		return $this->calculate($this->assetId_, $action, $identities);
	}

	public function loadPermissions($recursive = false, $groups = null, $action = null )
	{
		$permissionsFromQuery = $this->getAssetPermissions($recursive, $groups, $action);

		$this->prefillMatrix($permissionsFromQuery);
	}

	protected function calculate($asset, $action, $identities)
	{
		// Implicit deny by default.
		$result = null;

		// Check that the inputs are valid.
		if (!empty($identities))
		{
			if (!is_array($identities))
			{
				$identities = array($identities);
			}

			foreach ($identities as $identity)
			{
				// Technically the identity just needs to be unique.
				$identity = (int) $identity;

				// Check if the identity is known.
				if (isset(parent::$authorizationMatrix[self::IMPLEMENTATION][$asset][$action][$identity]))
				{
					$result = (boolean) parent::$authorizationMatrix[self::IMPLEMENTATION][$asset][$action][$identity];

					// An explicit deny wins.
					if ($result === false)
					{
						break;
					}
				}
			}
		}

		return $result;
	}


	/**
	 * Query permissions based on asset id.
	 *
	 * @param   boolean  $recursive  True to return the rules object with inherited rules.
	 * @param   array    $groups     Array of group ids to get permissions for
	 * @param   string   $action     Action name to limit results
	 *
	 * @return mixed   Db query result - the return value or null if the query failed.
	 *                 	 *
	 * @since   4.0
	 */
	private function getAssetPermissions($recursive = false, $groups = array(), $action = null)
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

		$query->select($prefix . '.id, p.permission, p.value, ' . $this->db->qn('p') . '.' . $this->db->qn('group'));
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
	 * Query root asset permissions
	 *
	 * @return mixed   Db query result - the return value or null if the query failed.
	 */
	public function getRootAssetPermissions()
	{
		$query = $this->db->getQuery(true);
		$query  ->select('b.id, p.permission, p.value, ' . $this->db->qn('p'). '.' . $this->db->qn('group'))
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
	 * @return  void
	 *
	 * @since   4.0
	 */
	private function prefillMatrix($results)
	{
		parent::$authorizationMatrix[self::IMPLEMENTATION] = array();

		foreach ($results AS $result)
		{
			if (isset($result->permission) && !empty($result->permission))
			{
				if (!isset(parent::$authorizationMatrix[self::IMPLEMENTATION][$result->id]))
				{
					parent::$authorizationMatrix[self::IMPLEMENTATION][$result->id] = array();
				}

				if (!isset(parent::$authorizationMatrix[self::IMPLEMENTATION][$result->id][$result->permission]))
				{
					parent::$authorizationMatrix[self::IMPLEMENTATION][$result->id][$result->permission] = array();
				}

				parent::$authorizationMatrix[self::IMPLEMENTATION][$result->id][$result->permission][$result->group] = (int) $result->value;
			}
		}
	}

	/** Inject permissions filter in database object
    *
    * @TODO make filter usable by passing asset name
	* @return  object database query object
	 *                 	 *
	 * @since   4.0
    */
	public function appendFilterQuery(&$query, $joinfield, $permission, $orWhere = null, $groups = null)
	{

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
				$gquery .= 'p.group = ' . $this->db->quote((string) $group);
				$gquery .= ($counter < $allgroups) ? ' OR ' : ' ) ';
				$counter++;
			}

			$conditions .= $gquery;
		}

		$conditions .= ' AND p.permission = ' . $this->db->quote($permission) . ' ';
		$query->leftJoin('#__permissions AS p ' . $conditions);

		// Magic
		$basicwhere = 'p.permission = ' . $this->db->quote($permission) . ' AND p.value=1';

		if (isset($orWhere))
		{
			$basicwhere = '(' . $basicwhere . ' OR ' . $orWhere . ')';
		}

		$query->where($basicwhere);

		$query->where('bs.level = (SELECT max(fs.level) FROM #__assets AS fs
  							LEFT JOIN #__permissions AS pr
 							ON fs.id = pr.assetid 
 						 	WHERE (ass.lft BETWEEN fs.lft AND fs.rgt) AND pr.permission = ' . $this->db->quote($permission) . ')');

		return $query;
	}
}
