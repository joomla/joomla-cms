<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of user records.
 *
 * @since  1.6
 */
class UsersModelUsers extends JModelList
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
				'name', 'a.name',
				'username', 'a.username',
				'email', 'a.email',
				'block', 'a.block',
				'sendEmail', 'a.sendEmail',
				'registerDate', 'a.registerDate',
				'lastvisitDate', 'a.lastvisitDate',
				'activation', 'a.activation',
				'active',
				'group_id',
				'range',
				'lastvisitrange',
				'state',
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
	protected function populateState($ordering = 'a.name', $direction = 'asc')
	{
		$app = JFactory::getApplication('administrator');

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout', 'default', 'cmd'))
		{
			$this->context .= '.' . $layout;
		}

		// Load the filter state.
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.active', $this->getUserStateFromRequest($this->context . '.filter.active', 'filter_active', '', 'cmd'));
		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'cmd'));
		$this->setState('filter.group_id', $this->getUserStateFromRequest($this->context . '.filter.group_id', 'filter_group_id', null, 'int'));
		$this->setState('filter.range', $this->getUserStateFromRequest($this->context . '.filter.range', 'filter_range', '', 'cmd'));
		$this->setState(
			'filter.lastvisitrange', $this->getUserStateFromRequest($this->context . '.filter.lastvisitrange', 'filter_lastvisitrange', '', 'cmd')
		);

		$groups = json_decode(base64_decode($app->input->get('groups', '', 'BASE64')));

		if (isset($groups))
		{
			$groups = ArrayHelper::toInteger($groups);
		}

		$this->setState('filter.groups', $groups);

		$excluded = json_decode(base64_decode($app->input->get('excluded', '', 'BASE64')));

		if (isset($excluded))
		{
			$excluded = ArrayHelper::toInteger($excluded);
		}

		$this->setState('filter.excluded', $excluded);

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
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.active');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.group_id');
		$id .= ':' . $this->getState('filter.range');

		return parent::getStoreId($id);
	}

	/**
	 * Gets the list of users and adds expensive joins to the result set.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItems()
	{
		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (empty($this->cache[$store]))
		{
			$groups  = $this->getState('filter.groups');
			$groupId = $this->getState('filter.group_id');

			if (isset($groups) && (empty($groups) || $groupId && !in_array($groupId, $groups)))
			{
				$items = array();
			}
			else
			{
				$items = parent::getItems();
			}

			// Bail out on an error or empty list.
			if (empty($items))
			{
				$this->cache[$store] = $items;

				return $items;
			}

			// Joining the groups with the main query is a performance hog.
			// Find the information only on the result set.

			// First pass: get list of the user id's and reset the counts.
			$userIds = array();

			foreach ($items as $item)
			{
				$userIds[] = (int) $item->id;
				$item->group_count = 0;
				$item->group_names = '';
				$item->note_count = 0;
			}

			// Get the counts from the database only for the users in the list.
			$db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Join over the group mapping table.
			$query->select('map.user_id, COUNT(map.group_id) AS group_count')
				->from('#__user_usergroup_map AS map')
				->where('map.user_id IN (' . implode(',', $userIds) . ')')
				->group('map.user_id')
				// Join over the user groups table.
				->join('LEFT', '#__usergroups AS g2 ON g2.id = map.group_id');

			$db->setQuery($query);

			// Load the counts into an array indexed on the user id field.
			try
			{
				$userGroups = $db->loadObjectList('user_id');
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());

				return false;
			}

			$query->clear()
				->select('n.user_id, COUNT(n.id) As note_count')
				->from('#__user_notes AS n')
				->where('n.user_id IN (' . implode(',', $userIds) . ')')
				->where('n.state >= 0')
				->group('n.user_id');

			$db->setQuery($query);

			// Load the counts into an array indexed on the aro.value field (the user id).
			try
			{
				$userNotes = $db->loadObjectList('user_id');
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());

				return false;
			}

			// Second pass: collect the group counts into the master items array.
			foreach ($items as &$item)
			{
				if (isset($userGroups[$item->id]))
				{
					$item->group_count = $userGroups[$item->id]->group_count;

					// Group_concat in other databases is not supported
					$item->group_names = $this->_getUserDisplayedGroups($item->id);
				}

				if (isset($userNotes[$item->id]))
				{
					$item->note_count = $userNotes[$item->id]->note_count;
				}
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
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);

		$query->from($db->quoteName('#__users') . ' AS a');

		// If the model is set to check item state, add to the query.
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where('a.block = ' . (int) $state);
		}

		// If the model is set to check the activated state, add to the query.
		$active = $this->getState('filter.active');

		if (is_numeric($active))
		{
			if ($active == '0')
			{
				$query->where('a.activation IN (' . $db->quote('') . ', ' . $db->quote('0') . ')');
			}
			elseif ($active == '1')
			{
				$query->where($query->length('a.activation') . ' > 1');
			}
		}

		// Filter the items over the group id if set.
		$groupId = $this->getState('filter.group_id');
		$groups  = $this->getState('filter.groups');

		if ($groupId || isset($groups))
		{
			$query->join('LEFT', '#__user_usergroup_map AS map2 ON map2.user_id = a.id')
				->group(
					$db->quoteName(
						array(
							'a.id',
							'a.name',
							'a.username',
							'a.password',
							'a.block',
							'a.sendEmail',
							'a.registerDate',
							'a.lastvisitDate',
							'a.activation',
							'a.params',
							'a.email'
						)
					)
				);

			if ($groupId)
			{
				$query->where('map2.group_id = ' . (int) $groupId);
			}

			if (isset($groups))
			{
				$query->where('map2.group_id IN (' . implode(',', $groups) . ')');
			}
		}

		// Filter the items over the search string if set.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			elseif (stripos($search, 'username:') === 0)
			{
				$search = $db->quote('%' . $db->escape(substr($search, 9), true) . '%');
				$query->where('a.username LIKE ' . $search);
			}
			else
			{
				// Escape the search token.
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));

				// Compile the different search clauses.
				$searches   = array();
				$searches[] = 'a.name LIKE ' . $search;
				$searches[] = 'a.username LIKE ' . $search;
				$searches[] = 'a.email LIKE ' . $search;

				// Add the clauses to the query.
				$query->where('(' . implode(' OR ', $searches) . ')');
			}
		}

		// Add filter for registration ranges select list
		$range = $this->getState('filter.range');

		// Apply the range filter.
		if ($range)
		{
			$dates = $this->buildDateRange($range);

			if ($dates['dNow'] === false)
			{
				$query->where(
					$db->qn('a.registerDate') . ' < ' . $db->quote($dates['dStart']->format('Y-m-d H:i:s'))
				);
			}
			else
			{
				$query->where(
					$db->qn('a.registerDate') . ' >= ' . $db->quote($dates['dStart']->format('Y-m-d H:i:s')) .
					' AND ' . $db->qn('a.registerDate') . ' <= ' . $db->quote($dates['dNow']->format('Y-m-d H:i:s'))
				);
			}
		}

		// Add filter for registration ranges select list
		$lastvisitrange = $this->getState('filter.lastvisitrange');

		// Apply the range filter.
		if ($lastvisitrange)
		{
			$dates = $this->buildDateRange($lastvisitrange);

			if (is_string($dates['dStart']))
			{
				$query->where(
					$db->qn('a.lastvisitDate') . ' = ' . $db->quote($dates['dStart'])
				);
			}
			elseif ($dates['dNow'] === false)
			{
				$query->where(
					$db->qn('a.lastvisitDate') . ' < ' . $db->quote($dates['dStart']->format('Y-m-d H:i:s'))
				);
			}
			else
			{
				$query->where(
					$db->qn('a.lastvisitDate') . ' >= ' . $db->quote($dates['dStart']->format('Y-m-d H:i:s')) .
					' AND ' . $db->qn('a.lastvisitDate') . ' <= ' . $db->quote($dates['dNow']->format('Y-m-d H:i:s'))
				);
			}
		}

		// Filter by excluded users
		$excluded = $this->getState('filter.excluded');

		if (!empty($excluded))
		{
			$query->where('id NOT IN (' . implode(',', $excluded) . ')');
		}

		// Add the list ordering clause.
		$query->order($db->qn($db->escape($this->getState('list.ordering', 'a.name'))) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * Construct the date range to filter on.
	 *
	 * @param   string  $range  The textual range to construct the filter for.
	 *
	 * @return  string  The date range to filter on.
	 *
	 * @since   3.6.0
	 */
	private function buildDateRange($range)
	{
		// Get UTC for now.
		$dNow   = new JDate;
		$dStart = clone $dNow;

		switch ($range)
		{
			case 'past_week':
				$dStart->modify('-7 day');
				break;

			case 'past_1month':
				$dStart->modify('-1 month');
				break;

			case 'past_3month':
				$dStart->modify('-3 month');
				break;

			case 'past_6month':
				$dStart->modify('-6 month');
				break;

			case 'post_year':
				$dNow = false;
			case 'past_year':
				$dStart->modify('-1 year');
				break;

			case 'today':
				// Ranges that need to align with local 'days' need special treatment.
				$app    = JFactory::getApplication();
				$offset = $app->get('offset');

				// Reset the start time to be the beginning of today, local time.
				$dStart = new JDate('now', $offset);
				$dStart->setTime(0, 0, 0);

				// Now change the timezone back to UTC.
				$tz = new DateTimeZone('GMT');
				$dStart->setTimezone($tz);
				break;
			case 'never':
				$dNow = false;
				$dStart = $this->_db->getNullDate();
				break;
		}

		return array('dNow' => $dNow, 'dStart' => $dStart);
	}

	/**
	 * SQL server change
	 *
	 * @param   integer  $user_id  User identifier
	 *
	 * @return  string   Groups titles imploded :$
	 */
	protected function _getUserDisplayedGroups($user_id)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('title'))
			->from($db->qn('#__usergroups', 'ug'))
			->join('LEFT', $db->qn('#__user_usergroup_map', 'map') . ' ON (ug.id = map.group_id)')
			->where($db->qn('map.user_id') . ' = ' . (int) $user_id);

		try
		{
			$result = $db->setQuery($query)->loadColumn();
		}
		catch (RunTimeException $e)
		{
			$result = array();
		}

		return implode("\n", $result);
	}
}
