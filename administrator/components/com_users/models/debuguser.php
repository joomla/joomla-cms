<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/helpers/debug.php';

/**
 * Methods supporting a list of user records.
 *
 * @since  1.6
 */
class UsersModelDebugUser extends JModelList
{
	/**
	 * Get a list of the actions.
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	public function getDebugActions()
	{
		$component = $this->getState('filter.component');

		return UsersHelperDebug::getDebugActions($component);
	}

	/**
	 * Override getItems method.
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	public function getItems()
	{
		$userId = $this->getState('filter.user_id');

		if (($assets = parent::getItems()) && $userId)
		{
			$actions = $this->getDebugActions();

			foreach ($assets as &$asset)
			{
				$asset->checks = array();

				foreach ($actions as $action)
				{
					$name = $action[0];
					$level = $action[1];

					// Check that we check this action for the level of the asset.
					if ($level === null || $level >= $asset->level)
					{
						// We need to test this action.
						$asset->checks[$name] = JAccess::check($userId, $name, $asset->name);
					}
					else
					{
						// We ignore this action.
						$asset->checks[$name] = 'skip';
					}
				}
			}
		}

		return $assets;
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
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication('administrator');

		// Adjust the context to support modal layouts.
		$layout = $app->input->get('layout', 'default');

		if ($layout)
		{
			$this->context .= '.' . $layout;
		}

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$value = $this->getUserStateFromRequest($this->context . '.filter.user_id', 'user_id', 0, 'int');
		$this->setState('filter.user_id', $value);

		$levelStart = $this->getUserStateFromRequest($this->context . '.filter.level_start', 'filter_level_start', 0, 'int');
		$this->setState('filter.level_start', $levelStart);

		$value = $this->getUserStateFromRequest($this->context . '.filter.level_end', 'filter_level_end', 0, 'int');

		if ($value > 0 && $value < $levelStart)
		{
			$value = $levelStart;
		}

		$this->setState('filter.level_end', $value);

		$component = $this->getUserStateFromRequest($this->context . '.filter.component', 'filter_component');
		$this->setState('filter.component', $component);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_users');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.lft', 'asc');
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
		$id .= ':' . $this->getState('filter.user_id');
		$id .= ':' . $this->getState('filter.level_start');
		$id .= ':' . $this->getState('filter.level_end');
		$id .= ':' . $this->getState('filter.component');

		return parent::getStoreId($id);
	}

	/**
	 * Get the user being debugged.
	 *
	 * @return  JUser
	 *
	 * @since   1.6
	 */
	public function getUser()
	{
		$userId = $this->getState('filter.user_id');

		return JFactory::getUser($userId);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
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
				'a.id, a.name, a.title, a.level, a.lft, a.rgt'
			)
		);
		$query->from($db->quoteName('#__assets') . ' AS a');

		// Filter the items over the group id if set.
		if ($groupId = $this->getState('filter.group_id'))
		{
			$query->join('LEFT', '#__user_usergroup_map AS map2 ON map2.user_id = a.id')
				->where('map2.group_id = ' . (int) $groupId);
		}

		// Filter the items over the search string if set.
		if ($this->getState('filter.search'))
		{
			// Escape the search token.
			$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($this->getState('filter.search')), true) . '%'));

			// Compile the different search clauses.
			$searches = array();
			$searches[] = 'a.name LIKE ' . $search;
			$searches[] = 'a.title LIKE ' . $search;

			// Add the clauses to the query.
			$query->where('(' . implode(' OR ', $searches) . ')');
		}

		// Filter on the start and end levels.
		$levelStart = (int) $this->getState('filter.level_start');
		$levelEnd = (int) $this->getState('filter.level_end');

		if ($levelEnd > 0 && $levelEnd < $levelStart)
		{
			$levelEnd = $levelStart;
		}

		if ($levelStart > 0)
		{
			$query->where('a.level >= ' . $levelStart);
		}

		if ($levelEnd > 0)
		{
			$query->where('a.level <= ' . $levelEnd);
		}

		// Filter the items over the component if set.
		if ($this->getState('filter.component'))
		{
			$component = $this->getState('filter.component');
			$query->where('(a.name = ' . $db->quote($component) . ' OR a.name LIKE ' . $db->quote($component . '.%') . ')');
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.lft')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}
}
