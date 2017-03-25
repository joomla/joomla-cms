<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * User notes model class.
 *
 * @since  2.5
 */
class UsersModelNotes extends JModelList
{
	/**
	 * Class constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since  2.5
	 */
	public function __construct($config = array())
	{
		// Set the list ordering fields.
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'user_id', 'a.user_id',
				'u.name',
				'subject', 'a.subject',
				'catid', 'a.catid', 'category_id',
				'state', 'a.state', 'published',
				'c.title',
				'review_time', 'a.review_time',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
				'level', 'c.level',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery  A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   2.5
	 */
	protected function getListQuery()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState('list.select',
				'a.id, a.subject, a.checked_out, a.checked_out_time,' .
				'a.catid, a.created_time, a.review_time,' .
				'a.state, a.publish_up, a.publish_down'
			)
		);
		$query->from('#__user_notes AS a');

		// Join over the category
		$query->select('c.title AS category_title, c.params AS category_params')
			->join('LEFT', '#__categories AS c ON c.id = a.catid');

		// Join over the users for the note user.
		$query->select('u.name AS user_name')
			->join('LEFT', '#__users AS u ON u.id = a.user_id');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')
			->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			elseif (stripos($search, 'uid:') === 0)
			{
				$query->where('a.user_id = ' . (int) substr($search, 4));
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('((a.subject LIKE ' . $search . ') OR (u.name LIKE ' . $search . ') OR (u.username LIKE ' . $search . '))');
			}
		}

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by a single or group of categories.
		$categoryId = $this->getState('filter.category_id');

		if ($categoryId && is_scalar($categoryId))
		{
			$query->where('a.catid = ' . $categoryId);
		}

		// Filter by a single user.
		$userId = (int) $this->getState('filter.user_id');

		if ($userId)
		{
			// Add the body and where filter.
			$query->select('a.body')
				->where('a.user_id = ' . $userId);
		}

		// Filter on the level.
		if ($level = $this->getState('filter.level'))
		{
			$query->where($db->quoteName('c.level') . ' <= ' . (int) $level);
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.review_time')) . ' ' . $db->escape($this->getState('list.direction', 'DESC')));

		return $query;
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
	 * @since   2.5
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.category_id');
		$id .= ':' . $this->getState('filter.user_id');
		$id .= ':' . $this->getState('filter.level');

		return parent::getStoreId($id);
	}

	/**
	 * Gets a user object if the user filter is set.
	 *
	 * @return  JUser  The JUser object
	 *
	 * @since   2.5
	 */
	public function getUser()
	{
		$user = new JUser;

		// Filter by search in title
		$search = (int) $this->getState('filter.user_id');

		if ($search != 0)
		{
			$user->load((int) $search);
		}

		return $user;
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
	protected function populateState($ordering = 'a.review_time', $direction = 'desc')
	{
		// Adjust the context to support modal layouts.
		if ($layout = JFactory::getApplication()->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search'));
		$this->setState('filter.published', $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '', 'string'));
		$this->setState('filter.category_id', $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id'));
		$this->setState('filter.user_id', $this->getUserStateFromRequest($this->context . '.filter.user_id', 'filter_user_id'));
		$this->setState('filter.level', $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level', '', 'cmd'));

		parent::populateState($ordering, $direction);
	}
}
