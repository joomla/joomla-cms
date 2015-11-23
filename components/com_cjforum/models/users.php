<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class CjForumModelUsers extends JModelList
{
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
				'posts',
				'thankyou', 'cju.thankyou',
				'rank', 'cju.rank',
				'rank_title',
				'active',
				'group_id',
				'range',
				'state',
			);
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout', 'default', 'cmd'))
		{
			$this->context .= '.' . $layout;
		}
		
		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);
		
		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$active = $this->getUserStateFromRequest($this->context . '.filter.active', 'filter_active');
		$this->setState('filter.active', $active);

		$state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state');
		$this->setState('filter.state', $state);

		$rankId = $this->getUserStateFromRequest($this->context . '.filter.rank', 'filter_rank', null, 'int');
		$this->setState('filter.rank', $rankId);
		
		$groupId = $this->getUserStateFromRequest($this->context . '.filter.group', 'filter_group_id', null, 'int');
		$this->setState('filter.group_id', $groupId);

		$range = $this->getUserStateFromRequest($this->context . '.filter.range', 'filter_range');
		$this->setState('filter.range', $range);

		$groups = json_decode(base64_decode($app->input->get('groups', '', 'BASE64')));

		if (isset($groups))
		{
			JArrayHelper::toInteger($groups);
		}

		$this->setState('filter.groups', $groups);

		$excluded = json_decode(base64_decode($app->input->get('excluded', '', 'BASE64')));

		if (isset($excluded))
		{
			JArrayHelper::toInteger($excluded);
		}

		$this->setState('filter.excluded', $excluded);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_users');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.name', 'asc');
	}

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

	public function getItems()
	{
		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (empty($this->cache[$store]))
		{
			$groups = $this->getState('filter.groups');
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
			$db = $this->getDbo();
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

	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		
		$topicFactor = 1;
		$replyFactor = 3;
		$thankyouFactor = 5;

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*,'.
				'cju.topics * '.$topicFactor.' + cju.replies * '.$replyFactor.' + cju.thankyou * '.$thankyouFactor.' AS karma,'.
				'cju.handle, cju.topics, cju.replies, cju.topics + cju.replies as posts, cju.fans, cju.thankyou,'.
				'cjr.title as rank_title'
			)
		);

		$query
			->from($db->quoteName('#__users') . ' AS a')
			->join('inner', $db->quoteName('#__cjforum_users') . ' AS cju on a.id = cju.id')
			->join('left', $db->quoteName('#__cjforum_ranks') . ' AS cjr on cju.rank = cjr.id');

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
				$query->where('a.activation = ' . $db->quote(''));
			}
			elseif ($active == '1')
			{
				$query->where($query->length('a.activation') . ' = 32');
			}
		}
		
		$rankId = $this->getState('filter.rank');
		
		if($rankId)
		{
			$query->where('cju.rank = '.$rankId);
		}

		// Filter the items over the group id if set.
		$groupId = $this->getState('filter.group_id');
		$groups = $this->getState('filter.groups');

		if ($groupId || isset($groups))
		{
			$query->join('LEFT', '#__user_usergroup_map AS map2 ON map2.user_id = a.id')
				->group($db->quoteName(array('a.id', 'a.name', 'a.username', 'a.password', 'a.block', 'a.sendEmail', 'a.registerDate', 'a.lastvisitDate', 'a.activation', 'a.params', 'a.email')));

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
		if ($this->getState('filter.search') !== '' && $this->getState('filter.search') !== null)
		{
			// Escape the search token.
			$token = $db->quote('%' . $db->escape($this->getState('filter.search')) . '%');

			// Compile the different search clauses.
			$searches = array();
			$searches[] = 'a.name LIKE ' . $token;
			$searches[] = 'a.username LIKE ' . $token;
			$searches[] = 'a.email LIKE ' . $token;

			// Add the clauses to the query.
			$query->where('(' . implode(' OR ', $searches) . ')');
		}

		// Add filter for registration ranges select list
		$range = $this->getState('filter.range');

		// Apply the range filter.
		if ($range)
		{
			// Get UTC for now.
			$dNow = new JDate;
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
				case 'past_year':
					$dStart->modify('-1 year');
					break;

				case 'today':
					// Ranges that need to align with local 'days' need special treatment.
					$app = JFactory::getApplication();
					$offset = $app->getCfg('offset');

					// Reset the start time to be the beginning of today, local time.
					$dStart = new JDate('now', $offset);
					$dStart->setTime(0, 0, 0);

					// Now change the timezone back to UTC.
					$tz = new DateTimeZone('GMT');
					$dStart->setTimezone($tz);
					break;
			}

			if ($range == 'post_year')
			{
				$query->where(
					'a.registerDate < ' . $db->quote($dStart->format('Y-m-d H:i:s'))
				);
			}
			else
			{
				$query->where(
					'a.registerDate >= ' . $db->quote($dStart->format('Y-m-d H:i:s')) .
						' AND a.registerDate <=' . $db->quote($dNow->format('Y-m-d H:i:s'))
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
		$query->order($db->escape($this->getState('list.ordering', 'a.name')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));
// echo $query->dump();
		return $query;
	}

	function _getUserDisplayedGroups($user_id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('title')
			->from('#__usergroups ug')
			->join('left', '#__user_usergroup_map map on (ug.id = map.group_id)')
			->where('map.user_id=' . (int) $user_id);

		$db->setQuery($query);
		$result = $db->loadColumn();

		return implode("\n", $result);
	}
}