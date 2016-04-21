<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Methods supporting a list of user group records.
 *
 * @since  1.6
 */
class UsersModelGroups extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'parent_id', 'a.parent_id',
				'title', 'a.title',
				'lft', 'a.lft',
				'rgt', 'a.rgt',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = 'a.lft', $direction = 'asc')
	{
		// Load the filter state.
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));

		// Load the parameters.
		$params = JComponentHelper::getParams('com_users');
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');

		return parent::getStoreId($id);
	}

	/**
	 * Gets the list of groups and adds expensive joins to the result set.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItems()
	{
		$db = $this->getDbo();

		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (empty($this->cache[$store]))
		{
			$items = parent::getItems();

			// Bail out on an error or empty list.
			if (empty($items))
			{
				$this->cache[$store] = $items;

				return $items;
			}

			// First pass: get list of the group id's and reset the counts.
			$groupIds = array();

			foreach ($items as $item)
			{
				$groupIds[] = (int) $item->id;
			}

			// Get total enabled users in group.
			$query = $db->getQuery(true);

			// Count the objects in the user group.
			$query->select('map.group_id, COUNT(DISTINCT map.user_id) AS user_count')
				->from($db->quoteName('#__user_usergroup_map', 'map'))
				->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('map.user_id'))
				->where($db->quoteName('map.group_id') . ' IN (' . implode(',', $groupIds) . ')')
				->where($db->quoteName('u.block') . ' = 0')
				->group($db->quoteName('map.group_id'));
			$db->setQuery($query);

			try
			{
				$countEnabled = $db->loadAssocList('group_id', 'count_enabled');
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());
				return false;
			}

			// Get total disabled users in group.
			$query->clear('where')
				->where('map.group_id IN (' . implode(',', $groupIds) . ')')
				->where('u.block = 1');
			$db->setQuery($query);

			try
			{
				$countDisabled = $db->loadAssocList('group_id', 'count_disabled');
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());
				return false;
			}

			// Inject the values back into the array.
			foreach ($items as $item)
			{
				$item->count_enabled   = isset($countEnabled[$item->id]) ? (int) $countEnabled[$item->id]['user_count'] : 0;
				$item->count_disabled  = isset($countDisabled[$item->id]) ? (int) $countDisabled[$item->id]['user_count'] : 0;
				$item->user_count      = $item->count_enabled + $item->count_disabled;
			}

			// Add the items to the internal cache.
			$this->cache[$store] = $items;
		}

		return $this->cache[$store];
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);
		$query->from($db->quoteName('#__usergroups') . ' AS a');

		// Add the level in the tree.
		$query->select('COUNT(DISTINCT c2.id) AS level')
			->join('LEFT OUTER', $db->quoteName('#__usergroups') . ' AS c2 ON a.lft > c2.lft AND a.rgt < c2.rgt')
			->group('a.id, a.lft, a.rgt, a.parent_id, a.title');

		// Filter the comments over the search string if set.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('a.title LIKE ' . $search);
			}
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.lft')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}
}
