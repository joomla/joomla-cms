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
 * Methods supporting a list of user records.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersModelUsers extends JModelList
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'com_users.users';

	/**
	 * Method to auto-populate the model state.
	 */
	protected function _populateState()
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->_context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$active = $app->getUserStateFromRequest($this->_context.'.filter.active', 'filter_active');
		$this->setState('filter.active', $active);

		$state = $app->getUserStateFromRequest($this->_context.'.filter.state', 'filter_state');
		$this->setState('filter.state', $state);

		$groupId = $app->getUserStateFromRequest($this->_context.'.filter.group', 'filter_group_id', null, 'int');
		$this->setState('filter.group_id', $groupId);

		// Load the parameters.
		$params		= JComponentHelper::getParams('com_users');
		$this->setState('params', $params);

		// List state information.
		parent::_populateState('a.name', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 *
	 * @return	string		A store id.
	 */
	protected function _getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.active');
		$id	.= ':'.$this->getState('filter.state');
		$id	.= ':'.$this->getState('filter.group_id');

		return parent::_getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JQuery
	 */
	protected function _getListQuery()
	{
		// Create a new query object.
		$query = new JQuery;

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);
		$query->from('`#__users` AS a');

		// Join over the group mapping table.
		$query->select('COUNT(map.group_id) AS group_count');
		$query->join('LEFT', '#__user_usergroup_map AS map ON map.user_id = a.id');
		$query->group('a.id');

		// Join over the user groups table.
		$query->select('GROUP_CONCAT(g2.title SEPARATOR '.$this->_db->Quote("\n").') AS group_names');
		$query->join('LEFT', '#__usergroups AS g2 ON g2.id = map.group_id');

		// If the model is set to check item state, add to the query.
		$state = $this->getState('filter.state');
		if (is_numeric($state)) {
			$query->where('a.block = '.(int) $state);
		}

		// If the model is set to check the activated state, add to the query.
		$active = $this->getState('filter.active');
		if (is_numeric($active))
		{
			if ($active == '0') {
				$query->where('a.activation = ""');
			}
			else if ($active == '1') {
				$query->where('LENGTH(a.activation) = 32');
			}
		}

		// Filter the items over the group id if set.
		if ($groupId = $this->getState('filter.group_id'))
		{
			$query->join('LEFT', '#__user_usergroup_map AS map2 ON map2.user_id = a.id');
			$query->where('map2.group_id = '.(int) $groupId);
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

		//echo nl2br(str_replace('#__','jos_',$query));
		return $query;
	}
}
