<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Access;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Class that handles all access authorisation routines.
 *
 * @since  __DEPLOY_VERSION__
 */
class AccessControl
{
	/**
	 * Array of the assets by id
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $assets = [];

	/**
	 * Array of asset ids by name
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $assetIdsByName = [];

	/**
	 * Array of rules for the asset by id
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $assetRules = [];

	/**
	 * Array of identities for asset rules by hash
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $assetRulesIdentities = [];

	/**
	 * Array of assets to preload, key is an asset id or name
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $assetsToPreload = [];

	/**
	 * Array of loaded user identities
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $identities = [];

	/**
	 * Array of cached groups by user.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $groupsByUser = [];

	/**
	 * Array of view levels
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $viewLevels = [];

	/**
	 * Array of the groups by id
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $groups = [];

	/**
	 *  Default value for number of table joins for assets
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	protected $defaultNestedForAssets = 5;

	/**
	 * Method to preload the Rules objects for all components.
	 *
	 * Note: This will only get the base permissions for the component.
	 * e.g. it will get 'com_content', but not 'com_content.article.1' or
	 * any more specific asset type rules.
	 *
	 * @return  boolean  True if assets were preloaded.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function preloadComponents()
	{
		if ($this->assets)
		{
			return false;
		}

		!JDEBUG ?: \JProfiler::getInstance('Application')->mark('Before AccessControl::preloadComponents');

		$components = ['root.1'];

		foreach (ComponentHelper::getComponents() as $component)
		{
			if ($component->enabled)
			{
				$components[] = $component->option;
			}
		}

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('id, name, rules, parent_id')
			->from($db->quoteName('#__assets'))
			->where('name IN (' . implode(',', $db->quote($components)) . ')');

		$it = $db->setQuery($query)->getIterator();

		foreach ($it as $row)
		{
			// Cast to integer at the beginning to save memory
			$row->id = (int) $row->id;
			$row->parent_id = (int) $row->parent_id;

			$this->assets[$row->id] = $row;
			$this->assetIdsByName[$row->name] = $row->id;
		}

		!JDEBUG ?: \JProfiler::getInstance('Application')->mark('After AccessControl::preloadComponents');

		return true;
	}

	/**
	 * Method to check if a user is authorised to perform an action, optionally on an asset.
	 * If an asset key is not found then extension asset will be used.
	 *
	 * @param   integer          $userId     Id of the user for which to check authorisation.
	 * @param   string           $action     The name of the action to authorise.
	 * @param   integer|string   $assetKey   The asset id or name or null to fallback to extension asset.
	 * @param   string           $extension  The name of the extension, ex 'com_content'.
	 * @param   boolean|integer  $nested     Indicates the level of optimalization. If True then default.
	 *
	 * @return  boolean|null  True if allowed, false for an explicit deny, null for an implicit deny.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function check($userId, $action, $assetKey = null, $extension = null, $nested = true)
	{
		// Sanitise input
		$userId = (int) $userId;

		if (!isset($this->identities[$userId]))
		{
			// Get all groups against which the user is mapped
			$this->identities[$userId] = $this->getGroupsByUser($userId);
			$this->identities[$userId][-$userId] = -$userId;
		}

		return $this->getAssetRules($assetKey, $extension, true, $nested)->allow($action, $this->identities[$userId]);
	}

	/**
	 * Method to recursively retrieve the list of parent Asset IDs
	 * for a particular Asset.
	 *
	 * @param   integer  $assetId  The numeric asset id.
	 *
	 * @return  array  List of ancestor ids (includes original $assetId).
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getAssetAncestors($assetId)
	{
		// Holds the list of ancestors for the Asset Id
		$ancestors = [];

		while (isset($this->assets[$assetId]))
		{
			$ancestors[] = $assetId;

			$assetId = $this->assets[$assetId]->parent_id;
		}

		return $ancestors;
	}

	/**
	 * Method to return the Rules object for an asset. The returned object can optionally hold
	 * only the rules explicitly set for the asset or the summation of all inherited rules from
	 * parent assets and explicit rules.
	 * If assetKey does not exists then rules from supplied extension will be used.
	 *
	 * @param   integer|string   $assetKey   The asset id, name or null to fallback to extension asset.
	 * @param   string           $extension  The name of the extension, ex 'com_content'.
	 * @param   boolean          $recursive  True to return the rules object with inherited rules.
	 * @param   boolean|integer  $nested     Indicates the level of optimalization. If True then default.
	 *
	 * @return  Rules  Rules object for the asset.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getAssetRules($assetKey, $extension = null, $recursive = false, $nested = true)
	{
		// Sanitise input
		$assetKey = $assetKey ?: null;

		$this->preloadComponents();

		if (is_numeric($assetKey))
		{
			$assetKey  = (int) $assetKey;
			$assetId   = $assetKey;
		}
		else
		{
			$assetId = null;

			if ($extension === null && $assetKey)
			{
				// Get fallabck extension name, e.g. 'com_content' from asset name 'com_content.article.1'
				list($extension) = explode('.', $assetKey, 2);
			}
		}

		if ($assetKey !== null)
		{
			// When $assetKey type is an integer, treat it as the asset id, otherwise as the asset name
			if (is_int($assetKey))
			{
				$this->addAssetIdToPreload($assetKey, $nested);
			}
			else
			{
				$this->addAssetNameToPreload($assetKey, $nested);
			}
		}

		$this->preloadAssets();

		// Get asset id after preloading assets
		if ($assetId === null && isset($this->assetIdsByName[$assetKey]))
		{
			$assetId = (int) $this->assetIdsByName[$assetKey];
		}

		if ($recursive)
		{
			// If asset id does not exist fallback to extension asset, then root asset
			if ($assetId === null || !isset($this->assets[$assetId]))
			{
				if (isset($this->assetIdsByName[$extension]))
				{
					$assetId = (int) $this->assetIdsByName[$extension];

					Log::add("No asset found for '$assetKey', falling back to '$extension'", Log::WARNING, 'assets');
				}
				else
				{
					// The root asset id
					$assetId = 1;

					Log::add("No asset found for '$assetKey', falling back to 'root.1'", Log::WARNING, 'assets');
				}
			}

			// If asset rules already cached em memory return it (only in full recursive mode).
			if (isset($this->assetRules[$assetId]))
			{
				return $this->assetRules[$assetId];
			}
		}

		// Collects permissions for each asset
		$collected = [];

		// If not in any recursive mode. We only want the asset rules.
		if (!$recursive)
		{
			$collected = [isset($this->assets[$assetId]) ? $this->assets[$assetId]->rules : '{}'];
		}
		// If there is any type of recursive mode.
		else
		{
			$ancestors = array_reverse($this->getAssetAncestors($assetId));

			foreach ($ancestors as $id)
			{
				// If empty asset to not add to rules.
				if ($this->assets[$id]->rules === '{}')
				{
					continue;
				}

				$collected[] = $this->assets[$id]->rules;
			}
		}

		/**
		* Hashing the collected rules allows us to store
		* only one instance of the Rules object for
		* Assets that have the same exact permissions...
		* it's a great way to save some memory.
		*/
		$hash = md5(implode(',', $collected));

		if (!isset($this->assetRulesIdentities[$hash]))
		{
			$rules = new Rules;
			$rules->mergeCollection($collected);

			$this->assetRulesIdentities[$hash] = $rules;
		}

		// Save asset rules to memory cache(only in full recursive mode).
		if ($recursive)
		{
			$this->assetRules[$assetId] = $this->assetRulesIdentities[$hash];
		}

		if (JDEBUG)
		{
			\JProfiler::getInstance('Application')->mark(
				'After AccessControl::getAssetRules (id:' . $assetId . ' name:' . $this->assets[$assetId]->name . ')'
			);
		}

		return $this->assetRulesIdentities[$hash];
	}

	/**
	 * Method to add asset id to preload list.
	 *
	 * @param   integer          $assetId  The numeric asset id.
	 * @param   boolean|integer  $nested   Indicates the level of optimalization. If True then default.
	 *
	 * @return  boolean  True if assets were preloaded.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function addAssetIdToPreload($assetId, $nested = true)
	{
		// Sanitise assetId
		$assetId = (int) $assetId;

		if ($assetId && !isset($this->assets[$assetId]))
		{
			// Sanitise the nested value, default 5, minimum 0
			$nested = $nested === true ? $this->defaultNestedForAssets : max(0, (int) $nested);

			$this->assetsToPreload['id'][$nested][$assetId] = $assetId;
		}
	}

	/**
	 * Method to add asset name to preload list.
	 *
	 * @param   string           $assetName  The asset name.
	 * @param   boolean|integer  $nested     Indicates the level of optimalization. If True then default.
	 *
	 * @return  boolean  True if assets were preloaded.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function addAssetNameToPreload($assetName, $nested = true)
	{
		if ($assetName && !isset($this->assetIdsByName[$assetName]))
		{
			// Sanitise the nested value, default 5, minimum 0
			$nested = $nested === true ? $this->defaultNestedForAssets : max(0, (int) $nested);

			$this->assetsToPreload['name'][$nested][$assetName] = $assetName;
		}
	}

	/**
	 * Method to preload the Rules objects for marked assets (include ancestors).
	 *
	 * @return  boolean  True if assets were preloaded.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function preloadAssets()
	{
		$total = 0;

		foreach ($this->assetsToPreload as $key => $assetsToPreloadByNested)
		{
			foreach ($assetsToPreloadByNested as $nested => $assetKeys)
			{
				$total += count($assetKeys);
			}
		}

		if ($total === 0)
		{
			return false;
		}

		$db = Factory::getDbo();

		$unionQuery = null;

		foreach ($this->assetsToPreload as $key => $assetsToPreloadByNested)
		{
			foreach ($assetsToPreloadByNested as $nested => $assetKeys)
			{
				if ($nested === 0)
				{
					$tbl = 'a0';

					$subquery = $db->getQuery(true)
						->from($db->quoteName('#__assets', $tbl));

					if ($key === 'id')
					{
						$subquery->where('a0.id IN (' . implode(', ', $assetKeys) . ')');
					}
					else
					{
						$subquery->where('a0.name IN (' . implode(', ', $db->quote($assetKeys)) . ')');
					}

					if ($total === count($assetKeys))
					{
						// No UNION
						$subquery->select("$tbl.id, $tbl.name, $tbl.rules, $tbl.parent_id, $tbl.level");

						// There is no more assets, set as 1 to skip LEFT JOIN
						$total = 1;
					}
					else
					{
						// All column will be joined later to speed up UNION DISTINCT
						$subquery->select("$tbl.id");
					}

					$unionQuery = $unionQuery === null ? $subquery : $unionQuery->union($subquery);
				}
				else
				{
					foreach ($assetKeys as $assetKey)
					{
						$tbl = 'a0';

						$subquery = $db->getQuery(true)
							->from($db->quoteName('#__assets', $tbl));

						$j = 0;

						// All ids from rows
						$aids = '';

						for ($i = 0; $i < $nested; $i++)
						{
							$j = $i + 1;
							$tbl = 'a' . $j;
							$aids .= 'a' . $i . '.id, ';

							if ($j === $nested)
							{
								$aids .= 'a' . $i . '.parent_id';

								$subquery->leftJoin(
									$db->quoteName('#__assets', $tbl)
									. " ON $tbl.id IN ($aids)"
								);
							}
							else
							{
								// Do not include the root asset
								$subquery->leftJoin(
									$db->quoteName('#__assets', $tbl)
									. " ON $tbl.id = a$i.parent_id AND $tbl.id != 1"
								);
							}
						}

						if ($total === 1)
						{
							// No UNION
							$subquery->select("$tbl.id, $tbl.name, $tbl.rules, $tbl.parent_id, $tbl.level");
						}
						else
						{
							// All column will be joined later to speed up UNION DISTINCT
							$subquery->select("$tbl.id");
						}

						if ($key === 'id')
						{
							$subquery->where("a0.id = " . $assetKey);
						}
						else
						{
							$subquery->where("a0.name = " . $db->quote($assetKey));
						}

						$unionQuery = $unionQuery === null ? $subquery : $unionQuery->union($subquery);
					}
				}
			}
		}

		if ($total === 1)
		{
			$query = $unionQuery->order("$tbl.lft");
		}
		else
		{
			// Add missing columns after UNION result
			$query = $db->getQuery(true)
				->from('(' . (string) $unionQuery . ') AS pks')
				->leftJoin($db->quoteName('#__assets', 'b') . ' ON b.id = pks.id')
				->select('b.id, b.name, b.rules, b.parent_id, b.level')
				->order('b.lft');
		}

		$it = $db->setQuery($query)->getIterator();

		// Reset lists
		$this->assetsToPreload = [];

		foreach ($it as $row)
		{
			$row->id        = (int) $row->id;
			$row->parent_id = (int) $row->parent_id;

			if (!isset($this->assets[$row->parent_id]))
			{
				// Set a nested variable based on the level value
				$this->assetsToPreload['id'][(int) $row->level - 3][] = $row->parent_id;
			}

			unset($row->level);

			if (!isset($this->assets[$row->id]))
			{
				$this->assets[$row->id]           = $row;
				$this->assetIdsByName[$row->name] = $row->id;
			}
		}

		if ($this->assetsToPreload)
		{
			return $this->preloadAssets();
		}

		return true;
	}

	/**
	 * Method to check if a group is authorised to perform an action, optionally on an asset.
	 *
	 * @param   integer         $groupId    The path to the group for which to check authorisation.
	 * @param   string          $action     The name of the action to authorise.
	 * @param   integer|string  $assetKey   The asset key (asset id or asset name). null fallback to root asset.
	 * @param   string          $extension  The name of the extension, ex 'com_content'.
	 * @param   boolean         $nested     Indicates whether preloading should be used.
	 *
	 * @return  boolean  True if authorised.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function checkGroup($groupId, $action, $assetKey = null, $extension = null, $nested = true)
	{
		return $this->getAssetRules($assetKey, $extension, true, $nested)
			->allow($action, $this->getGroupAncestors($groupId));
	}

	/**
	 * Method to recursively retrieve the list of parent Group IDs
	 * for a particular Group Id.
	 *
	 * @param   integer  $groupId  The group id.
	 *
	 * @return  array  List of ancestor ids (includes original $groupId).
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getGroupAncestors($groupId)
	{
		// Sanitise groupId
		$groupId = (int) $groupId;

		$this->loadAllGroups();

		// Holds the list of ancestors for the Asset Id
		$ancestors = [];

		while (isset($this->groups[$groupId]))
		{
			$ancestors[$groupId] = $groupId;

			$groupId = $this->groups[$groupId]->parent_id;
		}

		return $ancestors;
	}

	/**
	 * Get the list of existing user groups.
	 *
	 * @param   boolean  $populate  True to add a level property to all groups.
	 *
	 * @return  array  List of group objects.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getAllGroups($populate = false)
	{
		$this->loadAllGroups();

		if ($populate && !isset(current($this->groups)->level))
		{
			foreach ($this->groups as $group)
			{
				$group->level = $group->parent_id ? $this->groups[$group->parent_id]->level + 1 : 0;
			}
		}

		return $this->groups;
	}

	/**
	 * Load all user groups from the database.
	 *
	 * @return  boolean  True if groups were loaded.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function loadAllGroups()
	{
		if ($this->groups)
		{
			return false;
		}

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('id, parent_id, lft, rgt, title')
			->from($db->quoteName('#__usergroups'))
			->order('lft');

		$it = $db->setQuery($query)->getIterator();

		foreach ($it as $row)
		{
			$row->id        = (int) $row->id;
			$row->parent_id = (int) $row->parent_id;

			$this->groups[$row->id] = $row;
		}

		return true;
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function getGroupsByUser($userId, $recursive = true)
	{
		// Creates a simple unique string for each parameter combination:
		$storeId = $userId . ':' . (int) $recursive;

		if (isset($this->groupsByUser[$storeId]))
		{
			return $this->groupsByUser[$storeId];
		}

		// TODO: Uncouple this from ComponentHelper and allow for a configuration setting or value injection.
		$guestUsergroup = ComponentHelper::getParams('com_users')->get('guest_usergroup', 1);

		if (!$userId)
		{
			if ($recursive)
			{
				$this->groupsByUser[$storeId] = $this->getGroupAncestors($guestUsergroup);
			}
			else
			{
				// Guest user (if only the actually assigned group is requested)
				$this->groupsByUser[$storeId] = [$guestUsergroup => $guestUsergroup];
			}

			return $this->groupsByUser[$storeId];
		}

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('group_id')
			->from($db->quoteName('#__user_usergroup_map'))
			->where('user_id = ' . (int) $userId);

		$groupIds = $db->setQuery($query)->loadColumn();

		// Array of unique group ids
		$result = [];

		// Registered user and guest if all groups are requested
		if ($recursive)
		{
			foreach ($groupIds as $groupId)
			{
				foreach ($this->getGroupAncestors($groupId) as $gid)
				{
					// Group id as a key and value to eliminate duplicates
					$result[$gid] = $gid;
				}
			}
		}
		else
		{
			$this->loadAllGroups();

			foreach ($groupIds as $groupId)
			{
				$gid = (int) $groupId;

				// To be sure that this group exists
				if (isset($this->groups[$gid]))
				{
					$result[$gid] = $gid;
				}
			}
		}

		if (!$result)
		{
			// Fallback to guest user group
			$result = [$guestUsergroup => $guestUsergroup];
		}

		$this->groupsByUser[$storeId] = $result;

		return $result;
	}

	/**
	 * Method to return a list of view levels for which the user is authorised.
	 *
	 * @param   integer  $userId  Id of the user for which to get the list of authorised view levels.
	 *
	 * @return  array    List of view levels for which the user is authorised.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getAuthorisedViewLevels($userId)
	{
		// Only load the view levels once
		if (!$this->viewLevels)
		{
			// Get a database object
			$db = Factory::getDbo();

			// Build the base query.
			$query = $db->getQuery(true)
				->select('id, rules')
				->from($db->quoteName('#__viewlevels'));

			// Set the query for execution
			$db->setQuery($query);

			// Build the view levels array
			foreach ($db->loadRowList() as $row)
			{
				$this->viewLevels[(int) $row[0]] = (array) json_decode($row[1]);
			}
		}

		// Public access
		$access = (int) Factory::getConfig()->get('access', 1);

		// Initialise the authorised array
		$authorised = [$access => $access];

		// Check for the recovery mode setting and return early
		$root_user = Factory::getConfig()->get('root_user');

		if ($root_user)
		{
			$user = \Joomla\CMS\User\User::getInstance($userId);

			if (($user->username && $user->username == $root_user)
				|| (is_numeric($root_user) && $user->id > 0 && $user->id == $root_user))
			{
				// Find the super user levels
				foreach ($this->viewLevels as $level => $rules)
				{
					foreach ($rules as $id)
					{
						if ($id > 0 && $this->checkGroup($id, 'core.admin'))
						{
							$authorised[$level] = $level;
							break;
						}
					}
				}

				return $authorised;
			}
		}

		// Get all groups that the user is mapped to recursively
		$userGroups = $this->getGroupsByUser($userId);

		// Find the authorised levels
		foreach ($this->viewLevels as $level => $rules)
		{
			foreach ($rules as $id)
			{
				// Check to see if the group is mapped to the level
				if ($id > 0)
				{
					if (isset($userGroups[$id]))
					{
						$authorised[$level] = $level;
						break;
					}
				}
				elseif ($id < 0 && -$id == $userId)
				{
					$authorised[$level] = $level;
					break;
				}
			}
		}

		return $authorised;
	}

	/**
	 * Method to return a list of actions from a file for which permissions can be set.
	 *
	 * @param   string  $file   The path to the XML file.
	 * @param   string  $xpath  An optional xpath to search for the fields.
	 *
	 * @return  boolean|array   False if case of error or the list of actions available.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getActionsFromFile($file, $xpath = "/access/section[@name='component']/")
	{
		if (!is_file($file) || !is_readable($file))
		{
			// If unable to find the file return false.
			return false;
		}

		// Return the actions from the xml.
		$xml = simplexml_load_file($file);

		return self::getActionsFromData($xml, $xpath);
	}

	/**
	 * Method to return a list of actions from a string or from an xml for which permissions can be set.
	 *
	 * @param   string|\SimpleXMLElement  $data   The XML string or an XML element.
	 * @param   string                    $xpath  An optional xpath to search for the fields.
	 *
	 * @return  boolean|array   False if case of error or the list of actions available.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getActionsFromData($data, $xpath = "/access/section[@name='component']/")
	{
		// If the data to load isn't already an XML element or string return false.
		if ((!($data instanceof \SimpleXMLElement)) && (!is_string($data)))
		{
			return false;
		}

		// Attempt to load the XML if a string.
		if (is_string($data))
		{
			try
			{
				$data = new \SimpleXMLElement($data);
			}
			catch (\Exception $e)
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
		$actions = [];

		// Get the elements from the xpath
		$elements = $data->xpath($xpath . 'action[@name][@title]');

		// If there some elements, analyse them
		if (!empty($elements))
		{
			foreach ($elements as $element)
			{
				// Add the action to the actions array
				$action = array(
					'name' => (string) $element['name'],
					'title' => (string) $element['title'],
				);

				if (isset($element['description']))
				{
					$action['description'] = (string) $element['description'];
				}

				$actions[] = (object) $action;
			}
		}

		// Finally return the actions array
		return $actions;
	}
}
