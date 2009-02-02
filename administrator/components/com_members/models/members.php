<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');
jimport('joomla.database.query');

/**
 * Members Model for Members.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_members
 * @version		1.6
 */
class MembersModelMembers extends JModelList
{
	/**
	 * Model context string.
	 *
	 * @access	protected
	 * @var		string
	 */
	 var $_context = 'members.members';

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @access	protected
	 * @return	string	An SQL query
	 * @since	1.0
	 */
	function _getListQuery()
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

		// Join over the ARO groups table.
		$query->select('GROUP_CONCAT(g2.title SEPARATOR '.$this->_db->Quote("\n").') AS group_names');
		$query->join('LEFT', '#__usergroups AS g2 ON g2.id = map.group_id');

		// If the model is set to check item state, add to the query.
		if ($this->getState('check.state', true)) {
			$query->where('a.block = ' . (int)$this->getState('filter.state'));
		}

		// Filter the items over the group id if set.
		$group_id = $this->getState('filter.group_id');
		if ($group_id !== null && $group_id > 0) {
			$query->join('LEFT', '#__user_usergroup_map AS map2 ON map2.user_id = a.id');
			$query->where('map2.group_id = '.(int) $group_id);
		}

		// Filter the items over the search string if set.
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$query->where('(a.name LIKE '.$this->_db->Quote('%'.$search.'%').' OR a.username LIKE '.$this->_db->Quote('%'.$search.'%').')');
		}

		// Add the list ordering clause.
		$query->order($this->_db->getEscaped($this->getState('list.ordering', 'a.username')).' '.$this->_db->getEscaped($this->getState('list.direction', 'ASC')));

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
	 * @access	protected
	 * @param	string		$context	A prefix for the store id.
	 * @return	string		A store id.
	 * @since	1.0
	 */
	function _getStoreId($id = '')
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
	 * @access	protected
	 * @return	void
	 * @since	1.0
	 */
	function _populateState()
	{
		// Initialize variables.
		$app		= &JFactory::getApplication('administrator');
		$user		= &JFactory::getUser();
		$params		= JComponentHelper::getParams('com_members');
		$context	= 'com_members.members.';

		// Load the filter state.
		$this->setState('filter.search', $app->getUserStateFromRequest($context.'filter.search', 'filter_search', ''));
		$this->setState('filter.state', $app->getUserStateFromRequest($context.'filter.state', 'filter_state', '*', 'string'));
		$this->setState('filter.group_id', $app->getUserStateFromRequest($context.'filter.group_id', 'filter_group_id', null, 'int'));

		// Load the list state.
		$this->setState('list.start', $app->getUserStateFromRequest($context.'list.start', 'limitstart', 0, 'int'));
		$this->setState('list.limit', $app->getUserStateFromRequest($context.'list.limit', 'limit', $app->getCfg('list_limit', 25), 'int'));
		$this->setState('list.ordering', $app->getUserStateFromRequest($context.'list.ordering', 'filter_order', 'a.id', 'cmd'));
		$this->setState('list.direction', $app->getUserStateFromRequest($context.'list.direction', 'filter_order_Dir', 'ASC', 'word'));

		// Load the user parameters.
		$this->setState('user',	$user);
		$this->setState('user.id', (int)$user->id);
		$this->setState('user.aid', (int)$user->get('aid'));

		// Load the check parameters.
		if ($this->_state->get('filter.state') === '*') {
			$this->setState('check.state', false);
		} else {
			$this->setState('check.state', true);
		}

		// Load the parameters.
		$this->setState('params', $params);
	}
}
