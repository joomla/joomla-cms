<?php
/**
 * @version $Id: model.php 4526 2006-08-15 01:18:17Z webImagery $
 * @package Joomla
 * @subpackage Menus
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport( 'joomla.application.component.model' );

/**
 * @package Joomla
 * @subpackage Menus
 * @author Andrew Eddie
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
		$db = &$this->getDBO();

		// Preselect some aggregate data

		// Query to get published menu item counts
		$query = "SELECT a.menutype, COUNT( a.menutype ) AS num" .
				"\n FROM #__menu AS a" .
				"\n WHERE a.published = 1" .
				"\n GROUP BY a.menutype";
		$db->setQuery( $query );
		$published = $db->loadObjectList( 'menutype' );

		// Query to get unpublished menu item counts
		$query = "SELECT a.menutype, COUNT( a.menutype ) AS num" .
				"\n FROM #__menu AS a" .
				"\n WHERE a.published = 0" .
				"\n GROUP BY a.menutype";
		$db->setQuery( $query );
		$unpublished = $db->loadObjectList( 'menutype' );

		// Query to get trash menu item counts
		$query = "SELECT a.menutype, COUNT( a.menutype ) AS num" .
				"\n FROM #__menu AS a" .
				"\n WHERE a.published = -2" .
				"\n GROUP BY a.menutype";
		$db->setQuery( $query );
		$trash = $db->loadObjectList( 'menutype' );

		$menuTypes 	= JMenuHelper::getMenuTypeList();
		$total		= count( $menuTypes );
		$i			= 0;
		for ($i = 0;  $i < $total; $i++) {
			$row = &$menuTypes[$i];

			// query to get number of modules for menutype
			$query = "SELECT count( id )" .
					"\n FROM #__modules" .
					"\n WHERE module = 'mod_mainmenu'" .
					"\n AND params LIKE '%" . $row->menutype . "%'";
			$db->setQuery( $query );
			$modules = $db->loadResult();

			if ( !$modules ) {
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

		$menutypes 	= JMenuHelper::getMenuTypeList();
		$total		= count( $menutypes );
		$limit		= $mainframe->getUserStateFromRequest("com_menus.limit", 'limit', $mainframe->getCfg('list_limit'), 0);
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

		jimport('joomla.html.pagination');
		$pagination = new JPagination( $total, $limitstart, $limit );
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
			$table->menutype = JRequest::getVar('menutype');
		}

		$db = &$this->getDBO();
		$query = "SELECT a.name, a.id" .
				"\n FROM #__menu AS a" .
				"\n WHERE a.menutype = " . $db->Quote( $table->menutype ) .
				"\n ORDER BY a.name";
		$db->setQuery( $query );
		$result = $db->loadObjectList();
		return $result;
	}

	/**
	 * Get a list of the menu records associated with the type
	 * @param string The menu type
	 * @return array An array of records as objects
	 */
	function getModules( $type='' )
	{
		if ($type == '') {
			$type = $this->_table->menutype;
		}

		$db = &$this->getDBO();
		$query = "SELECT id, title, params" .
				"\n FROM #__modules" .
				"\n WHERE module = 'mod_mainmenu'" .
				"\n AND params LIKE " . $db->Quote( '%menutype=' . $type . '%' );
		$db->setQuery( $query );
		$temp = $db->loadObjectList();

		$result = array();
		$n = count( $temp );
		for ($i = 0; $i < $n; $i++)
		{
			$params = new JParameter( $temp[$i]->params );
			if ($params->get( 'menutype' ) == $type) {
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
	function canDelete( $type='' )
	{
		if ($type == '') {
			$type = $this->_table->menutype;
		}
		if ($type == 'mainmenu') {
			$this->setError( JText::_( 'WARNDELMAINMENU' ) );
			return false;
		}
		return true;
	}

	/**
	 * Deletes menu type and associations
	 * @param string The id of the menu type
	 * @return boolean
	 */
	function delete( $id = 0 )
	{
		$table = &$this->getTable();
		if ($id != 0) {
			$table->load( $id );
		}

		$db = &$this->getDBO();

		// Delete Associations
		if (!$this->deleteByType( $table->menutype )) {
			$this->setError( $menu->getError() );
			return false;
		}

		// TODO: Should invoke JModuleModel::delete to delete the actual module
		$moduleTable= &JTable::getInstance( 'module');
		$items		= &$this->getModules( $table->menutype );
		$modulesIds	= array();
		foreach ($items as $item)
		{
			if (!$moduleTable->delete( $item->id )) {
				$this->setError( $moduleTable->getErrorMsg() );
				return false;
			}
			$modulesIds[] = $item->id;
		}

		if (count( $modulesIds )) {
			$query = "DELETE FROM #__modules_menu" .
					"\n WHERE menuid = ".implode( ' OR moduleid = ', $modulesIds );
			$db->setQuery( $query );
			if (!$db->query()) {
				$this->setError( $menuTable->getErrorMsg() );
				return false;
			}
		}

		$result = $table->delete();

		return $result;
	}

	/**
	 * Delete menu items by type
	 */
	function deleteByType( $type = '' )
	{
		if (!$type) {
			return false;
		}
		$db = &$this->getDBO();
		$query = "DELETE FROM #__menu" .
				"\n WHERE menutype = ".$db->Quote( $type );
		$db->setQuery( $query );
		if (!$db->query()) {
			$this->setError( $menuTable->getErrorMsg() );
			return false;
		}
		return true;
	}
}
?>