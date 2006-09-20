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

jimport( 'joomla.application.model' );

/**
 * @package Joomla
 * @subpackage Menus
 * @author Andrew Eddie
 */
class JModelMenuType extends JModel
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
			$this->_table = & JTable::getInstance('menuTypes', $this->getDBO());
		}
		return $this->_table;
	}

	/**
	 * Get a list of the menu records associated with the type
	 * @param string The menu type
	 * @return array An array of records as objects
	 */
	function getMenuItems( $type='' )
	{
		if ($type == '')
		{
			$type = $this->_table->menutype;
		}

		$db = &$this->getDBO();
		$query = "SELECT a.name, a.id"
		. "\n FROM #__menu AS a"
		. "\n WHERE a.menutype = " . $db->Quote( $type )
		. "\n ORDER BY a.name"
		;
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
		if ($type == '')
		{
			$type = $this->_table->menutype;
		}

		$db = &$this->getDBO();
		$query = "SELECT id, title, params"
		. "\n FROM #__modules"
		. "\n WHERE module = 'mod_mainmenu'"
		. "\n AND params LIKE " . $db->Quote( '%menutype=' . $type . '%' )
		;
		$db->setQuery( $query );
		$temp = $db->loadObjectList();

		$result = array();
		$n = count( $temp );
		for ($i = 0; $i < $n; $i++)
		{
			$params = new JParameter( $temp[$i]->params );
			if ($params->get( 'menutype' ) == $type)
			{
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
		$moduleTable= &JTable::getInstance( 'module', $db );
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
			$query = 'DELETE FROM #__modules_menu'
				. ' WHERE menuid = ' . implode( ' OR moduleid = ', $modulesIds );
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
		$db = &$this->getDBO();

		$query = 'SELECT id' .
				' FROM #__menu' .
				' WHERE menutype = ' . $db->Quote( $type );
		$db->setQuery( $query );
		$ids = $db->loadResultArray();

		if ($db->getErrorNum()) {
			$this->setError( $db->getErrorMsg() );
			return false;
		}

		return $this->delete( $ids );
	}

}
?>