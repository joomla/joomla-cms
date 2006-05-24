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

jimport( 'joomla.application.controller' );

/**
 * @package  Joomla
 * @subpackage Menus
 */
class MenuTypeController extends JController
{
	var $_option = 'com_menumanager';

	/**
	 * @return object A Menu Type model object
	 */
	function &getModel() {
		$model = &JModel::getInstance( 'JMenuTypeModel' );
		return $model;
	}

	/**
	 * Controller for view listing menu types and related statical info
	 * @param string The URL option
	 */
	function listItems()
	{
		// TODO: following line will eventually be jimport( 'application.model.menu' ); or similar
		require_once( JPATH_ADMINISTRATOR . '/components/com_menus/model.php' );
	
		$database	= &$this->getDBO();
		$mainframe	= &$this->getApplication();
	
		$menus		= array();
		$limit 		= $mainframe->getUserStateFromRequest( 'limit', 'limit',  $mainframe->getCfg('list_limit') );
		$limitstart = $mainframe->getUserStateFromRequest( $this->_option . '.limitstart', 'limitstart', 0 );
	
		// Preselect some aggregate data
	
		// Query to get published menu item counts
		$query = "SELECT a.menutype, COUNT( a.menutype ) AS num"
		. "\n FROM #__menu AS a"
		. "\n WHERE a.published = 1"
		. "\n GROUP BY a.menutype"
		;
		$database->setQuery( $query );
		$published = $database->loadObjectList( 'menutype' );
	
		// Query to get unpublished menu item counts
		$query = "SELECT a.menutype, COUNT( a.menutype ) AS num"
		. "\n FROM #__menu AS a"
		. "\n WHERE a.published = 0"
		. "\n GROUP BY a.menutype"
		;
		$database->setQuery( $query );
		$unpublished = $database->loadObjectList( 'menutype' );
	
		// Query to get trash menu item counts
		$query = "SELECT a.menutype, COUNT( a.menutype ) AS num"
		. "\n FROM #__menu AS a"
		. "\n WHERE a.published = -2"
		. "\n GROUP BY a.menutype"
		;
		$database->setQuery( $query );
		$trash = $database->loadObjectList( 'menutype' );
	
		$model		= &JModel::getInstance( 'JMenuModel' );
		$menuTypes 	= $model->getMenuTypeList();
	
		$total		= count( $menuTypes );
		$i			= 0;
		for ($i = 0;  $i < $total; $i++) {
			$row = &$menuTypes[$i];
	
			// query to get number of modules for menutype
			$query = "SELECT count( id )"
			. "\n FROM #__modules"
			. "\n WHERE module = 'mod_mainmenu'"
			. "\n AND params LIKE '%" . $row->menutype . "%'"
			;
			$database->setQuery( $query );
			$modules = $database->loadResult();
	
			if ( !$modules ) {
				$modules = '-';
			}
			$row->modules		= $modules;
			$row->published		= @$published[$row->menutype]->num ? $published[$row->menutype]->num : '-' ;
			$row->unpublished	= @$unpublished[$row->menutype]->num ? $unpublished[$row->menutype]->num : '-';
			$row->trash			= @$trash[$row->menutype]->num ? $trash[$row->menutype]->num : '-';
		}
	
		jimport('joomla.presentation.pagination');
		$pageNav = new JPagination( $total, $limitstart, $limit );

		$view = new JMenuManagerListView( $this );
		$view->display( $menuTypes, $pageNav );
	}
	
	
	/**
	 * Controller for view to create or edit a menu type
	 */
	function edit() {
		global $database, $task;
	
		$id	= (int) JRequest::getVar( 'id', 0 );
	
		if ($task == 'new')
		{
			$id = 0;
		}
	
		$model = &$this->getModel();
		$table = &$model->getTable();

		$table->load( $id );

		$view = new JMenuManagerEditView( $this );
		$view->setModel( $model, true );
		$view->display();
	}
	
	/**
	 * Controller for saving a menu type
	 */
	function saveMenu() {
		global $database;
	
		$id		= (int) JRequest::getVar( 'id', 0 );
	
		$oldType = new JTableMenuTypes( $database );
		$oldType->load( $id );
	
		$menuType = new JTableMenuTypes( $database );
		$menuType->bind( $_POST );
	
		$isNew		= ($menuType->id == 0);
		$isChanged	= ($oldType->menutype != $menuType->menutype);
	
		// block to stop renaming of 'mainmenu' menutype
		if ($oldType->menutype == 'mainmenu' && $isChanged) {
			josErrorAlert( JText::_( 'WARNMAINMENU', true ) );
		}
	
		if (!$menuType->check()) {
			josErrorAlert( $menuType->getError() );
			exit;
		}
	
		if (!$menuType->store())
		{
			josErrorAlert( $menuType->getError() );
			exit;
		}
	
		if ($isNew) {
			$title = JRequest::getVar( 'module_title', $menuType->menutype, 'post' );
	
			$module =& JTable::getInstance( 'module', $database );
			$module->title 		= $title;
			$module->position 	= 'left';
			$module->module 	= 'mod_mainmenu';
			$module->published	= 0;
			$module->iscore 	= 0;
			$module->params		= 'menutype='. $menuType->menutype;
	
			// check then store data in db
			if (!$module->check()) {
				josErrorAlert( $module->getError() );
				exit();
			}
			if (!$module->store()) {
				josErrorAlert( $module->getError() );
				exit();
			}
			$module->checkin();
			$module->reorder( "position='". $module->position ."'" );
	
			// module assigned to show on All pages by default
			// ToDO: Changed to become a Joomla! db-object
			$query = "INSERT INTO #__modules_menu VALUES ( $module->id, 0 )";
			$database->setQuery( $query );
			if ( !$database->query() ) {
				josErrorAlert( $database->getErrorMsg() );
				exit();
			}
	
	    	$msg = sprintf( JText::_( 'New Menu created' ), $menuType->menutype );
		}
		else if ($isChanged)
		{
			$oldTerm = 'menutype=' . $oldType->menutype;
			$newTerm = 'menutype=' . $menuType->menutype;
	
			// change menutype being of all mod_mainmenu modules calling old menutype
			$query = "SELECT id"
			. "\n FROM #__modules"
			. "\n WHERE module = 'mod_mainmenu'"
			. "\n AND params LIKE '%menutype=$oldTerm%'"
			;
			$database->setQuery( $query );
			$modules = $database->loadResultArray();
	
			foreach ($modules as $id) {
				$row =& JTable::getInstance('module', $database );
				$row->load( $id );
	
				$row->params = str_replace( $oldTerm, $newTerm, $row->params );
	
				// check then store data in db
				if ( !$row->check() ) {
					josErrorAlert( $row->getError() );
					exit();
				}
				if ( !$row->store() ) {
					josErrorAlert( $row->getError() );
					exit();
				}
				$row->checkin();
			}
	
			// change menutype of all menuitems using old menutype
			$query = "UPDATE #__menu"
			. "\n SET menutype = '$menutype'"
			. "\n WHERE menutype = '$old_menutype'"
			;
			$database->setQuery( $query );
			$database->query();
	
			$msg = JText::_( 'Menu Items & Modules updated' );
		}
	
		josRedirect( 'index2.php?option=com_menumanager', $msg );
	}
	
	/**
	 * Controller for a view to confirm the deletion of a menu type
	 */
	function delete() {
		$id = (int) JRequest::getVar( 'id', 0 );

		if ($id <= 0)
		{
			JError::raiseError( 500, JText::_( 'Invalid ID provided' ) );
			return false;
		}

		$model = &$this->getModel();
		$table = &$model->getTable();

		if (!$table->load( $id ))
		{
			JError::raiseError( 500, $table->getError() );
			return false;
		}
	
		if (!$model->canDelete()) {
			JError::raiseError( 500, $model->getError() );
			return false;
		}
	
		if ($this->getTask() == 'delete')
		{
			$err = '';

			if (!$model->delete())
			{
				 $err = $model->getError();
			}
			
			$this->setRedirect( 'index2.php?option=' . $this->_option, $err );
		}
		else
		{
			// list of menu items to delete
			$menuItems = $model->getMenuItems();
		
			// list of modules to delete
			$modules = $model->getModules();
			
			$view = new JMenuManagerConfirmDeleteView( $this );
			$view->setModel( $model, true );
			$view->display();
		}
	}
	
	/**
	 * Deletes  menu items(s) you have selected
	 */
	function deleteMenu( $option, $cid, $type ) {
		global $database;

		$id = (int) JRequest::getVar( 'cid', 0 );
	
		if ( $type == 'mainmenu' ) {
			josErrorAlert( JText::_( 'WARNDELMAINMENU', true ) );
			exit();
		}
	
	
		$mids = JRequest::getVar( 'mids', 0, 'post' );
		if ( is_array( $mids ) ) {
			$mids = implode( ',', $mids );
		}
		// delete menu items
		$query = "DELETE FROM #__menu"
		. "\n WHERE ( id IN ( $mids ) )"
		;
		$database->setQuery( $query );
		if ( !$database->query() ) {
			josErrorAlert( $database->getErrorMsg() );
			exit;
		}
	
		if ( is_array( $cid ) ) {
			$cids = implode( ',', $cid );
		} else {
			$cids = $cid;
		}
	
		// checks whether any modules to delete
		if ( $cids ) {
			// delete modules
			$query = "DELETE FROM #__modules"
			. "\n WHERE id IN ( $cids )"
			;
			$database->setQuery( $query );
			if ( !$database->query() ) {
				josErrorAlert( $database->getErrorMsg() );
				exit;
			}
			// delete all module entires in jos_modules_menu
			$query = "DELETE FROM #__modules_menu"
			. "\n WHERE moduleid IN ( $cids )"
			;
			$database->setQuery( $query );
			if ( !$database->query() ) {
				josErrorAlert( $database->getErrorMsg() );
				exit;
			}
	
			// reorder modules after deletion
			$mod =& JTable::getInstance('module', $database );
			$mod->ordering = 0;
			$mod->reorder( "position='left'" );
			$mod->reorder( "position='right'" );
		}
	
		$msg = JText::_( 'Menu Deleted' );
		josRedirect( 'index2.php?option=' . $option, $msg );
	}
	
	
	/**
	* Compiles a list of the items you have selected to Copy
	*/
	function copyConfirm( $option, $type ) {
		global $database;
	
		// Content Items query
		$query = 	"SELECT a.name, a.id"
		. "\n FROM #__menu AS a"
		. "\n WHERE ( a.menutype = ( '$type' ) )"
		. "\n ORDER BY a.name"
		;
		$database->setQuery( $query );
		$items = $database->loadObjectList();
	
		$view = new JMenuManagerConfirmCopyView( $this );
		$view->display( $type, $items );
	}
	
	
	/**
	* Copies a complete menu, all its items and creates a new module, using the name speified
	*/
	function copyMenu( $option, $cid, $type ) {
		global $database;
	
		$menu_name 		= JRequest::getVar( 'menu_name', 'New Menu', 'post' );
		$module_name 	= JRequest::getVar( 'module_name', 'New Module', 'post' );
	
		// check for unique menutype for new menu copy
		$query = "SELECT params"
		. "\n FROM #__modules"
		. "\n WHERE module = 'mod_mainmenu'"
		;
		$database->setQuery( $query );
		$menus = $database->loadResultArray();
		foreach ( $menus as $menu ) {
			$params = mosParseParams( $menu );
			if ( $params->menutype == $menu_name ) {
				josErrorAlert( JText::_( 'ERRORMENUNAMEEXISTS', true ) );
				exit;
			}
		}
	
		// copy the menu items
		$mids 		= JRequest::getVar( 'mids', array(), 'post', 'array' );
		$total 		= count( $mids );
		$copy 		=& JTable::getInstance('menu', $database );
		$original 	=& JTable::getInstance('menu', $database );
		sort( $mids );
		$a_ids 		= array();
	
		foreach( $mids as $mid ) {
			$original->load( $mid );
			$copy 			= $original;
			$copy->id 		= NULL;
			$copy->parent 	= $a_ids[$original->parent];
			$copy->menutype = $menu_name;
	
			if ( !$copy->check() ) {
				josErrorAlert( $copy->getError() );
				exit();
			}
			if ( !$copy->store() ) {
				josErrorAlert( $copy->getError() );
				exit();
			}
			$a_ids[$original->id] = $copy->id;
		}
	
		// create the module copy
		$row =& JTable::getInstance('module', $database );
		$row->load( 0 );
		$row->title 	= $module_name;
		$row->iscore 	= 0;
		$row->published = 1;
		$row->position 	= 'left';
		$row->module 	= 'mod_mainmenu';
		$row->params 	= 'menutype='. $menu_name;
	
		if (!$row->check()) {
			josErrorAlert( $row->getError() );
			exit();
		}
		if (!$row->store()) {
			josErrorAlert( $row->getError() );
			exit();
		}
		$row->checkin();
		$row->reorder( "position='$row->position'" );
		// module assigned to show on All pages by default
		// ToDO: Changed to become a Joomla! db-object
		$query = "INSERT INTO #__modules_menu VALUES ( $row->id, 0 )";
		$database->setQuery( $query );
		if ( !$database->query() ) {
			echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
			exit();
		}
	
		$msg = sprintf( JText::_( 'Copy of Menu created' ), $type, $total );
		josRedirect( 'index2.php?option=' . $option, $msg );
	}
	
	/**
	* Cancels an edit operation
	* @param option	options for the operation
	*/
	function cancel() {
		$this->setRedirect( 'index2.php?option=com_menumanager' );
	}
}
?>