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
 * Menu Item List Model for Menus.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @since		1.6
 */
class MenusModelItems extends JModelList
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'com_menus.items';

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return	void
	 */
	protected function _populateState()
	{
		$app = &JFactory::getApplication('administrator');

		$search = $app->getUserStateFromRequest($this->_context.'.search', 'search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->_context.'.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$access = $app->getUserStateFromRequest($this->_context.'.filter.access', 'filter_access', 0, 'int');
		$this->setState('filter.access', $access);

		$parentId = $app->getUserStateFromRequest($this->_context.'.filter.parent_id', 'filter_parent_id', 0, 'int');
		$this->setState('filter.parent_id',	$parentId);

		$level = $app->getUserStateFromRequest($this->_context.'.filter.level', 'filter_level', 0, 'int');
		$this->setState('filter.level', $level);

		$menuType = $app->getUserStateFromRequest($this->_context.'.filter.menutype', 'menutype', 'mainmenu');
		$this->setState('filter.menutype', $menuType);

		// List state information.
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$this->setState('list.limit', $limit);

		$limitstart = $app->getUserStateFromRequest($this->_context.'.limitstart', 'limitstart', 0);
		$this->setState('list.start', $limitstart);

		$orderCol	= $app->getUserStateFromRequest($this->_context.'.ordercol', 'filter_order', 'a.lft');
		$this->setState('list.ordering', $orderCol);

		$orderDirn	= $app->getUserStateFromRequest($this->_context.'.orderdirn', 'filter_order_Dir', 'asc');
		$this->setState('list.direction', $orderDirn);

		// Component parameters.
		$params	= &JComponentHelper::getParams('com_menus');
		$this->setState('params', $params);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 * @return	string		A store id.
	 */
	protected function _getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('list.start');
		$id	.= ':'.$this->getState('list.limit');
		$id	.= ':'.$this->getState('list.ordering');
		$id	.= ':'.$this->getState('list.direction');
		$id	.= ':'.$this->getState('filter.access');
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.parent_id');
		$id	.= ':'.$this->getState('filter.menu_id');

		return md5($id);
	}

	/**
	 * Builds an SQL query to load the list data.
	 *
	 * @return	JQuery	A query object.
	 */
	protected function _getListQuery()
	{
		// Create a new query object.
		$query = new JQuery;

		// Select all fields from the table.
		$query->select($this->getState('list.select', 'a.*'));
		$query->from('`#__menu` AS a');

		// Join over the users.
		$query->select('u.name AS editor');
		$query->join('LEFT', '`#__users` AS u ON u.id = a.checked_out');

		//Join over components
		$query->select('c.name AS componentname');
		$query->join('LEFT', '`#__components` AS c ON c.id = a.component_id');



		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Self join to find the level in the tree.
		$query->select('COUNT(DISTINCT p.id) AS level');
		$query->join('LEFT OUTER', '`#__menu` AS p ON a.lft > p.lft AND a.rgt < p.rgt');
		$query->group('a.id');

		// Exclude the root category.
		$query->where('a.id > 1');

		// Filter on the published state.
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.published = '.(int) $published);
		}
		else if ($published === '') {
			$query->where('(a.published IN (0, 1))');
		}

		// Filter by search in title, alias or id
		if ($search = trim($this->getState('filter.search')))
		{
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			}
			else if (stripos($search, 'link:') === 0)
			{
				if ($search = substr($search, 5)) {
					$search = $this->_db->Quote('%'.$this->_db->getEscaped($search, true).'%');
					$query->where('a.link LIKE '.$search);
				}
			}
			else {
				$search = $this->_db->Quote('%'.$this->_db->getEscaped($search, true).'%');
				$query->where('a.title LIKE '.$search.' OR a.alias LIKE '.$search);
			}
		}

		// Filter the items over the parent id if set.
		$parentId = $this->getState('filter.parent_id');
		if (!empty($parentId)) {
			$query->where('p.id = '.(int)$parentId);
		}

		// Filter the items over the menu id if set.
		$menuId = $this->getState('filter.menu_id');
		if (!empty($menuId)) {
			$query->where('a.menu_id = '.(int) $menuId);
		}

		// Filter the items over the menu id if set.
		$menuType = $this->getState('filter.menutype');
		if (!empty($menuType)) {
			$query->where('a.menutype = '.$this->_db->quote($menuType));
		}

		// Filter on the access level.
		if ($access = $this->getState('filter.access')) {
			$query->where('a.access = '.(int) $access);
		}

		// Filter on the level.
		if ($level = $this->getState('filter.level')) {
			$query->where('a.level <= '.(int) $level);
		}

		// Add the list ordering clause.
		$query->order($this->_db->getEscaped($this->getState('list.ordering', 'a.lft')).' '.$this->_db->getEscaped($this->getState('list.direction', 'ASC')));

		//echo nl2br(str_replace('#__','jos_',$query->toString())).'<hr/>';
		return $query;
	}
}
