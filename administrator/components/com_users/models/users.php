<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');
jimport('joomla.database.query');

/**
 * Users Model for Users.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @version		1.0
 */
class UsersModelUsers extends JModelList
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	 protected $_context = 'users.users';

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 */
	protected function _getListQuery()
	{
		// Create a new query object.
		$query = new JQuery;

		// Select all fields from the users table.
		$query->select($this->getState('list.select', 'a.*'));
		$query->from('`#__users` AS a');

		// Join over the ARO mapping table.
		$query->select('COUNT(map.group_id) AS group_count');
		$query->join('LEFT', '#__user_usergroup_map AS map ON map.user_id = a.id');
		$query->group('a.id');

		// Join over the user groups table.
		$query->select('GROUP_CONCAT(g2.title SEPARATOR '.$this->_db->Quote("\n").') AS group_names');
		$query->join('LEFT', '#__usergroups AS g2 ON g2.id = map.group_id');

		// If the model is set to check item state, add to the query.
		if ($this->getState('filter.state') !== '') {
			$query->where('a.block = ' . (int)$this->getState('filter.state'));
		}

		// If the model is set to check the activated state, add to the query.
		if ($this->getState('filter.active') !== '') {
			$active = $this->getState('filter.active');
			if ($active == '0') {
				$query->where('a.activation = ""');
			} elseif ($active == '1') {
				$query->where('LENGTH(a.activation) = 32');
			}
		}

		// Filter the items over the group id if set.
		$group_id = $this->getState('filter.group');
		if ($group_id !== null && $group_id > 0) {
			$query->join('LEFT', '#__user_usergroup_map AS map2 ON map2.user_id = a.id');
			$query->where('map2.group_id = '.(int) $group_id);
		}

		// Filter the items over the search string if set.
		if ($this->getState('filter.search') !== '')
		{
			// Escape the search token.
			$token	= $this->_db->Quote('%'.$this->_db->getEscaped($this->getState('filter.search')).'%');

			// Compile the different search clauses.
			$searches	= array();
			$searches[]	= 'a.name LIKE '.$token;
			$searches[]	= 'a.username LIKE '.$token;
			$searches[]	= 'a.email LIKE '.$token;

			// Add the clauses to the query.
			$query->where('('.implode(' OR ', $searches).')');
		}

		// Add the list ordering clause.
		$query->order($this->_db->getEscaped($this->getState('list.ordering', 'a.name')).' '.$this->_db->getEscaped($this->getState('list.direction', 'ASC')));

		//echo nl2br(str_replace('#__','jos_',$query->toString())).'<hr/>';
		return $query;
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$context	A prefix for the store id.
	 * @return	string		A store id.
	 */
	protected function _getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('list.start');
		$id	.= ':'.$this->getState('list.limit');
		$id	.= ':'.$this->getState('list.ordering');
		$id	.= ':'.$this->getState('list.direction');
		$id	.= ':'.$this->getState('check.state');
		$id	.= ':'.$this->getState('filter.state');
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.group_id');

		return md5($id);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @return	void
	 */
	protected function _populateState()
	{
		// Initialize variables.
		$app		= JFactory::getApplication('administrator');
		$user		= JFactory::getUser();
		$params		= JComponentHelper::getParams('com_users');
		$context	= 'com_users.users.';

		// Load the filter state.
		$this->setState('filter.search', $app->getUserStateFromRequest($context.'filter.search', 'filter_search', ''));
		$this->setState('filter.state', $app->getUserStateFromRequest($context.'filter.state', 'filter_state', '', 'alnum'));
		$this->setState('filter.active', $app->getUserStateFromRequest($context.'filter.active', 'filter_active', '', 'alnum'));
		$this->setState('filter.group', $app->getUserStateFromRequest($context.'filter.group', 'filter_group', '', 'alnum'));

		// Load the list state.
		$this->setState('list.start', $app->getUserStateFromRequest($context.'list.start', 'limitstart', 0, 'int'));
		$this->setState('list.limit', $app->getUserStateFromRequest($context.'list.limit', 'limit', $app->getCfg('list_limit', 25), 'int'));
		$this->setState('list.ordering', $app->getUserStateFromRequest($context.'list.ordering', 'filter_order', 'a.name', 'cmd'));
		$this->setState('list.direction', $app->getUserStateFromRequest($context.'list.direction', 'filter_order_Dir', 'ASC', 'word'));

		// Load the user parameters.
		$this->setState('user',	$user);
		$this->setState('user.id', (int) $user->id);

		// Load the parameters.
		$this->setState('params', $params);
	}
}
