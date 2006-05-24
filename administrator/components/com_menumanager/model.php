<?php
/**
 * @version $Id: admin.menus.php 3504 2006-05-15 05:25:43Z eddieajau $
 * @package Joomla
 * @subpackage Menus
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
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
class JMenuTypeModel extends JModel
{
	/** @var object JTable object */
	var $_table = null;

	/**
	 * Returns the internal table object
	 * @return JTable
	 */
	function &getTable()
	{
		if ($this->_table == null)
		{
			jimport( 'joomla.database.table.menutypes' );

			$db = &$this->getDBO();
			$this->_table = new JTableMenuTypes( $db );
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
		if ($type == '')
		{
			$type = $this->_table->menutype;
		}
		if ($type == 'mainmenu')
		{
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
		if ($id != 0)
		{
			$table->load( $id );
		}

		$db = &$this->getDBO();
		// Delete Associations

		// TODO: following line will eventually be jimport( 'application.model.menu' ); or similar
		require_once( JPATH_ADMINISTRATOR . '/components/com_menus/model.php' );
		$menu = new JMenuModel( $db );

			if (!$menu->deleteByType( $table->menutype ))
			{
				$this->setError( $menu->getError() );
				return false;				
			}

		// TODO: Should invoke JModuleModel::delete to delete the actual module
		$moduleTable= &JTable::getInstance( 'module', $db );
		$items		= &$this->getModules( $table->menutype );
		$modulesIds	= array();
		foreach ($items as $item)
		{
			if (!$moduleTable->delete( $item->id ))
			{
				$this->setError( $moduleTable->getErrorMsg() );
				return false;
			}
			$modulesIds[] = $item->id;
		}

		if (count( $modulesIds ))
		{
			$query = 'DELETE FROM #__modules_menu'
				. ' WHERE menuid = ' . implode( ' OR moduleid = ', $modulesIds );
			$db->setQuery( $query );
			if (!$db->query())
			{
				$this->setError( $menuTable->getErrorMsg() );
				return false;				
			}
		}

		$result = $table->delete();

		return $result;
	}
}
?>
