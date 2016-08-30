<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Helper to deal with user groups.
 *
 * @since  DEPLOY_VERSION
 */
final class JHelperUsergroups
{
	/**
	 * @const  integer
	 */
	const MODE_SINGLETON = 1;

	/**
	 * @const  integer
	 */
	const MODE_INSTANCE = 2;

	/**
	 * Singleton instance.
	 *
	 * @var  array
	 */
	private static $instance;

	/**
	 * Available user groups
	 *
	 * @var  array
	 */
	private $groups = array();

	/**
	 * Mode this class is working: singleton or std instance
	 *
	 * @var  integer
	 */
	private $mode;

	/**
	 * Total available groups
	 *
	 * @var  integer
	 */
	private $total;

	/**
	 * Constructor
	 *
	 * @param   array    $groups  Array of groups
	 * @param   integer  $mode    Working mode for this class
	 */
	public function __construct(array $groups = array(), $mode = self::MODE_INSTANCE)
	{
		$this->mode = (int) $mode;

		if ($groups)
		{
			$this->setGroups($groups);
		}
	}

	/**
	 * Count loaded user groups.
	 *
	 * @return  integer
	 */
	public function count()
	{
		return count($this->groups);
	}

	/**
	 * Get the helper instance.
	 *
	 * @return  self
	 */
	public static function getInstance()
	{
		if (null === static::$instance)
		{
			// Only here to avoid code style issues...
			$groups = array();

			static::$instance = new static($groups, static::MODE_SINGLETON);
		}

		return static::$instance;
	}

	/**
	 * Get a user group by its id.
	 *
	 * @param   integer  $id  Group identifier
	 *
	 * @return  mixed         stdClass on success. False otherwise
	 */
	public function get($id)
	{
		if ($this->has($id))
		{
			return $this->groups[$id];
		}

		// Singleton will load groups as they are requested
		if ($this->isSingleton())
		{
			$this->groups[$id] = $this->load($id);

			return $this->groups[$id];
		}

		return false;
	}

	/**
	 * Get the list of existing user groups.
	 *
	 * @return  array
	 */
	public function getAll()
	{
		if ($this->isSingleton() && $this->total() !== $this->count())
		{
			$this->loadAll();
		}

		return $this->groups;
	}

	/**
	 * Check if a group is in the list.
	 *
	 * @param   integer  $id  Group identifier
	 *
	 * @return  boolean
	 */
	public function has($id)
	{
		return (array_key_exists($id, $this->groups) && false !== $this->groups[$id]);
	}

	/**
	 * Check if this instance is a singleton.
	 *
	 * @return  boolean
	 */
	private function isSingleton()
	{
		return $this->mode === static::MODE_SINGLETON;
	}

	/**
	 * Get total available user groups in database.
	 *
	 * @return  integer
	 */
	public function total()
	{
		if (null === $this->total)
		{
			$db = JFactory::getDbo();

			$query = $db->getQuery(true)
				->select('count(id)')
				->from('#__usergroups');

			$db->setQuery($query, 0, 1);

			$this->total = $db->loadResult();
		}

		return $this->total;
	}

	/**
	 * Load a group from database.
	 *
	 * @param   integer  $id  Group identifier
	 *
	 * @return  mixed
	 */
	public function load($id)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select('*')
			->from('#__usergroups')
			->where('id = ' . (int) $id);

		$db->setQuery($query);

		$group = $db->loadObject();

		if (!$group)
		{
			return false;
		}

		return $this->populateGroupData($group);
	}

	/**
	 * Load all user groups from the database.
	 *
	 * @return  self
	 */
	public function loadAll()
	{
		$this->groups = array();

		$db = \JFactory::getDbo();

		$query = $db->getQuery(true)
			->select('*')
			->from('#__usergroups')
			->order('lft ASC');

		$db->setQuery($query);

		$groups = $db->loadObjectList('id');

		$this->groups = $groups ? $groups : array();
		$this->populateGroupsData();

		return $this;
	}

	/**
	 * Populates extra information for groups.
	 *
	 * @return  array
	 */
	private function populateGroupsData()
	{
		foreach ($this->groups as $group)
		{
			$this->populateGroupData($group);
		}

		return $this->groups;
	}

	/**
	 * Populate data for a specific user group.
	 *
	 * @param   stdClass  $group  Group
	 *
	 * @return  stdClass
	 */
	public function populateGroupData($group)
	{
		if (!$group || property_exists($group, 'path'))
		{
			return $group;
		}

		$parentId = (int) $group->parent_id;

		if ($parentId === 0)
		{
			$group->path = array($group->id);
			$group->level = 0;

			return $group;
		}

		$parentGroup = $this->has($parentId) ? $this->get($parentId) : $this->load($parentId);

		$group->path = array_merge($parentGroup->path, array($group->id));
		$group->level = count($group->path) - 1;

		return $group;
	}

	/**
	 * Set the groups to be used as source.
	 *
	 * @param   array  $groups  Array of user groups.
	 *
	 * @return  self
	 */
	public function setGroups(array $groups)
	{
		$this->groups = $groups;
		$this->populateGroupsData();
		$this->total  = count($groups);

		return $this;
	}
}
