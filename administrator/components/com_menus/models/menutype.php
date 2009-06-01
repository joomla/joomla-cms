<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * @package		Joomla.Administrator
 * @subpackage	Menus
 */
class MenusModelMenutype extends JModel
{
	var $_modelName = 'menutype';

	/** @var object JTable object */
	var $_table = null;

	/**
	 * Returns the internal table object
	 * @return JTable
	 */
	function &getTable()
	{
		if ($this->_table == null) {
			$this->_table = & JTable::getInstance('menuTypes');
			if ($id = JRequest::getVar('id', false, '', 'int')) {
				$this->_table->load($id);
			}
		}
		return $this->_table;
	}

	/**
	 * Get a list of the menu records associated with the type
	 *
	 * @param string The menu type
	 * @return array An array of records as objects
	 */
	function getMenus()
	{
		global $mainframe;

		$menus= array();
		$db = &$this->getDbo();

		// Preselect some aggregate data

		// Query to get published menu item counts
		$query = 'SELECT a.menutype, COUNT(a.menutype) AS num' .
				' FROM #__menu AS a' .
				' WHERE a.published = 1' .
				' GROUP BY a.menutype';
		$db->setQuery($query);
		$published = $db->loadObjectList('menutype');

		// Query to get unpublished menu item counts
		$query = 'SELECT a.menutype, COUNT(a.menutype) AS num' .
				' FROM #__menu AS a' .
				' WHERE a.published = 0' .
				' GROUP BY a.menutype';
		$db->setQuery($query);
		$unpublished = $db->loadObjectList('menutype');

		// Query to get trash menu item counts
		$query = 'SELECT a.menutype, COUNT(a.menutype) AS num' .
				' FROM #__menu AS a' .
				' WHERE a.published = -2' .
				' GROUP BY a.menutype';
		$db->setQuery($query);
		$trash = $db->loadObjectList('menutype');

		$limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest('com_menus.limitstart', 'limitstart', 0, 'int');

		$query = 'SELECT a.*, SUM(b.home) AS home' .
				' FROM #__menu_types AS a' .
				' LEFT JOIN #__menu AS b ON b.menutype = a.menutype' .
				' GROUP BY a.id';
		$db->setQuery($query, $limitstart, $limit);
		$menuTypes	= $db->loadObjectList();

		$total		= count($menuTypes);
		$i			= 0;
		for ($i = 0;  $i < $total; $i++) {
			$row = &$menuTypes[$i];

			// query to get number of modules for menutype
			$query = 'SELECT count(id)' .
					' FROM #__modules' .
					' WHERE module = "mod_mainmenu"' .
					' AND params LIKE '.$db->Quote('%menutype='.$row->menutype.'%');
			$db->setQuery($query);
			$modules = $db->loadResult();

			if (!$modules) {
				$modules = '-';
			}
			$row->modules		= $modules;
			$row->published		= @$published[$row->menutype]->num ? $published[$row->menutype]->num : '-' ;
			$row->unpublished	= @$unpublished[$row->menutype]->num ? $unpublished[$row->menutype]->num : '-';
			$row->trash			= @$trash[$row->menutype]->num ? $trash[$row->menutype]->num : '-';
			$menus[] = $row;
		}
		return $menus;
	}

	/**
	 * Get a list of the menu records associated with the type
	 *
	 * @param string The menu type
	 * @return array An array of records as objects
	 */
	function getPagination()
	{
		global $mainframe;

		$menutypes 	= MenusHelper::getMenuTypeList();
		$total		= count($menutypes);
		$limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest('com_menus.limitstart', 'limitstart', 0, 'int');

		jimport('joomla.html.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);
		return $pagination;
	}

	/**
	 * Get a list of the menu records associated with the type
	 * @param string The menu type
	 * @return array An array of records as objects
	 */
	function getMenuItems()
	{
		$table = & $this->getTable();
		if ($table->menutype == '') {
			$table->menutype = JRequest::getString('menutype');
		}

		$db = &$this->getDbo();
		$query = 'SELECT a.name, a.id' .
				' FROM #__menu AS a' .
				' WHERE a.menutype = ' . $db->Quote($table->menutype) .
				' ORDER BY a.name';
		$db->setQuery($query);
		$result = $db->loadObjectList();
		return $result;
	}

	/**
	 * Get a list of the menu records associated with the type
	 * @param string The menu type
	 * @return array An array of records as objects
	 */
	function getModules($type='')
	{
		if ($type == '') {
			$type = $this->_table->menutype;
		}

		$db = &$this->getDbo();
		$query = 'SELECT id, title, params' .
				' FROM #__modules' .
				' WHERE module = "mod_mainmenu"' .
				' AND params LIKE ' . $db->Quote('%menutype=' . $type . '%');
		$db->setQuery($query);
		$temp = $db->loadObjectList();

		$result = array();
		$n = count($temp);
		for ($i = 0; $i < $n; $i++)
		{
			$params = new JParameter($temp[$i]->params);
			if ($params->get('menutype') == $type) {
				 $result[] = $temp[$i];
			}
		}
		return $result;
	}

	/**
	 * Checks if the menu can be deleted
	 * @param string The menu type
	 * @return boolean
	 */
	function canDelete($type='')
	{
		if ($type == '') {
			$type = $this->_table->menutype;
		}
		if ($type == 'mainmenu') {
			$this->setError(JText::_('WARNDELMAINMENU'));
			return false;
		}
		return true;
	}

	/**
	 * Deletes menu type and associations
	 * @param string The id of the menu type
	 * @return boolean
	 */
	function delete($id = 0)
	{
		$table = &$this->getTable();
		if ($id != 0) {
			$table->load($id);
		}

		$db = &$this->getDbo();

		// Delete Associations
		if (!$this->deleteByType($table->menutype)) {
			$this->setError($this->getError());
			return false;
		}

		// TODO: Should invoke JModuleModel::delete to delete the actual module
		$moduleTable= &JTable::getInstance('module');
		$items		= &$this->getModules($table->menutype);
		$modulesIds	= array();
		foreach ($items as $item)
		{
			if (!$moduleTable->delete($item->id)) {
				$this->setError($moduleTable->getErrorMsg());
				return false;
			}
			$modulesIds[] = (int) $item->id;
		}

		if (count($modulesIds)) {
			$query = 'DELETE FROM #__modules_menu' .
					' WHERE menuid = '.implode(' OR moduleid = ', $modulesIds);
			$db->setQuery($query);
			if (!$db->query()) {
				$this->setError($menuTable->getErrorMsg());
				return false;
			}
		}

		$result = $table->delete();

		return $result;
	}

	/**
	 * Delete menu items by type
	 */
	function deleteByType($type = '')
	{
		if (!$type) {
			return false;
		}
		$db = &$this->getDbo();
		$query = 'DELETE FROM #__menu' .
				' WHERE menutype = '.$db->Quote($type);
		$db->setQuery($query);
		if (!$db->query()) {
			$this->setError($menuTable->getErrorMsg());
			return false;
		}

		// clean cache
		MenusHelper::cleanCache();

		return true;
	}
}
