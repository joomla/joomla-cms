<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\Database\ParameterType;

/**
 * Helper to deal with user groups.
 *
 * @since  3.6.3
 */
final class UserGroupsHelper
{
	/**
	 * Indicates the current helper instance is the singleton instance.
	 *
	 * @var    integer
	 * @since  3.6.3
	 */
	const MODE_SINGLETON = 1;

	/**
	 * Indicates the current helper instance is a standalone class instance.
	 *
	 * @var    integer
	 * @since  3.6.3
	 */
	const MODE_INSTANCE = 2;

	/**
	 * Singleton instance.
	 *
	 * @var    array
	 * @since  3.6.3
	 */
	private static $instance;

	/**
	 * Available user groups
	 *
	 * @var    array
	 * @since  3.6.3
	 */
	private $groups = array();

	/**
	 * Mode this class is working: singleton or std instance
	 *
	 * @var    integer
	 * @since  3.6.3
	 */
	private $mode;

	/**
	 * Total available groups
	 *
	 * @var    integer
	 * @since  3.6.3
	 */
	private $total;

	/**
	 * Constructor
	 *
	 * @param   array    $groups  Array of groups
	 * @param   integer  $mode    Working mode for this class
	 *
	 * @since   3.6.3
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
	 *
	 * @since   3.6.3
	 */
	public function count()
	{
		return \count($this->groups);
	}

	/**
	 * Get the helper instance.
	 *
	 * @return  self
	 *
	 * @since   3.6.3
	 */
	public static function getInstance()
	{
		if (static::$instance === null)
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
	 * @return  mixed  stdClass on success. False otherwise
	 *
	 * @since   3.6.3
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
	 *
	 * @since   3.6.3
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
	 *
	 * @since   3.6.3
	 */
	public function has($id)
	{
		return (\array_key_exists($id, $this->groups) && $this->groups[$id] !== false);
	}

	/**
	 * Check if this instance is a singleton.
	 *
	 * @return  boolean
	 *
	 * @since   3.6.3
	 */
	private function isSingleton()
	{
		return $this->mode === static::MODE_SINGLETON;
	}

	/**
	 * Get total available user groups in database.
	 *
	 * @return  integer
	 *
	 * @since   3.6.3
	 */
	public function total()
	{
		if ($this->total === null)
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select('COUNT(' . $db->quoteName('id') . ')')
				->from($db->quoteName('#__usergroups'));

			$db->setQuery($query);

			$this->total = (int) $db->loadResult();
		}

		return $this->total;
	}

	/**
	 * Load a group from database.
	 *
	 * @param   integer  $id  Group identifier
	 *
	 * @return  mixed
	 *
	 * @since   3.6.3
	 */
	public function load($id)
	{
		// Cast as integer until method is typehinted.
		$id = (int) $id;

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__usergroups'))
			->where($db->quoteName('id') . ' = :id')
			->bind(':id', $id, ParameterType::INTEGER);

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
	 *
	 * @since   3.6.3
	 */
	public function loadAll()
	{
		$this->groups = array();

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__usergroups'))
			->order($db->quoteName('lft') . ' ASC');

		$db->setQuery($query);

		$groups = $db->loadObjectList('id');

		$this->groups = $groups ?: array();
		$this->populateGroupsData();

		return $this;
	}

	/**
	 * Populates extra information for groups.
	 *
	 * @return  array
	 *
	 * @since   3.6.3
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
	 * @param   \stdClass  $group  Group
	 *
	 * @return  \stdClass
	 *
	 * @since   3.6.3
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

		if (!property_exists($parentGroup, 'path'))
		{
			$parentGroup = $this->populateGroupData($parentGroup);
		}

		$group->path = array_merge($parentGroup->path, array($group->id));
		$group->level = \count($group->path) - 1;

		return $group;
	}

	/**
	 * Set the groups to be used as source.
	 *
	 * @param   array  $groups  Array of user groups.
	 *
	 * @return  self
	 *
	 * @since   3.6.3
	 */
	public function setGroups(array $groups)
	{
		$this->groups = $groups;
		$this->populateGroupsData();
		$this->total  = \count($groups);

		return $this;
	}
}
