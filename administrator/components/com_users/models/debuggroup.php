<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('UsersHelperDebug', JPATH_ADMINISTRATOR . '/components/com_users/helpers/debug.php');

/**
 * Methods supporting a list of User ACL permissions
 *
 * @since  1.6
 */
class UsersModelDebuggroup extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   3.6.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'a.title',
				'component', 'a.name',
				'a.lft',
				'a.id',
				'level_start', 'level_end', 'a.level',
			);
		}

		parent::__construct($config);
	}

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
		$groupId = $this->getState('group_id');

		if (($assets = parent::getItems()) && $groupId)
		{
			$actions = $this->getDebugActions();

			foreach ($assets as &$asset)
			{
				$asset->checks = array();

				foreach ($actions as $action)
				{
					$name = $action[0];

					$asset->checks[$name] = JAccess::checkGroup($groupId, $name, $asset->name);
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
	protected function populateState($ordering = 'a.lft', $direction = 'asc')
	{
		$app = JFactory::getApplication('administrator');

		// Adjust the context to support modal layouts.
		$layout = $app->input->get('layout', 'default');

		if ($layout)
		{
			$this->context .= '.' . $layout;
		}

		// Load the filter state.
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('group_id', $this->getUserStateFromRequest($this->context . '.group_id', 'group_id', 0, 'int', false));

		$levelStart = $this->getUserStateFromRequest($this->context . '.filter.level_start', 'filter_level_start', '', 'cmd');
		$this->setState('filter.level_start', $levelStart);

		$value = $this->getUserStateFromRequest($this->context . '.filter.level_end', 'filter_level_end', '', 'cmd');

		if ($value > 0 && $value < $levelStart)
		{
			$value = $levelStart;
		}

		$this->setState('filter.level_end', $value);

		$this->setState('filter.component', $this->getUserStateFromRequest($this->context . '.filter.component', 'filter_component', '', 'string'));

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
		$id .= ':' . $this->getState('group_id');
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.level_start');
		$id .= ':' . $this->getState('filter.level_end');
		$id .= ':' . $this->getState('filter.component');

		return parent::getStoreId($id);
	}

	/**
	 * Get the group being debugged.
	 *
	 * @return  JObject
	 *
	 * @since   1.6
	 */
	public function getGroup()
	{
		$groupId = (int) $this->getState('group_id');

		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('id, title')
			->from('#__usergroups')
			->where('id = ' . $groupId);

		$db->setQuery($query);

		try
		{
			$group = $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return $group;
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
		$query->from($db->quoteName('#__assets', 'a'));

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
