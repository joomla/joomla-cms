<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
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
				'id',
				'a.id',
				'user_id',
				'a.user_id',
				'u.name',
				'subject',
				'a.subject',
				'catid',
				'a.catid',
				'state', 'a.state',
				'c.title',
				'review_time',
				'a.review_time',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
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
		$section = $this->getState('filter.category_id');

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
		$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by a single or group of categories.
		$categoryId = (int) $this->getState('filter.category_id');

		if ($categoryId)
		{
			if (is_scalar($section))
			{
				$query->where('a.catid = ' . $categoryId);
			}
		}

		// Filter by a single user.
		$userId = (int) $this->getState('filter.user_id');

		if ($userId)
		{
			// Add the body and where filter.
			$query->select('a.body')
				->where('a.user_id = ' . $userId);
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

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
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.category_id');

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
		$search = JFactory::getApplication()->input->get('u_id', 0, 'int');

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
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();
		$input = $app->input;

		// Adjust the context to support modal layouts.
		if ($layout = $input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		$value = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $value);

		$published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		$section = $app->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id');
		$this->setState('filter.category_id', $section);

		$userId = $input->get('u_id', 0, 'int');
		$this->setState('filter.user_id', $userId);

		parent::populateState('a.review_time', 'DESC');
	}
}
