<?php
/**
 * @version $Id: admin.menus.php 3607 2006-05-24 01:09:39Z eddieajau $
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

jimport('joomla.application.component.controller');

/**
 * @package Joomla
 * @subpackage Menus
 */
class JMenuController extends JController
{
	/**
	 * New menu item wizard
	 */
	function type()
	{
		$model	=& $this->getModel( 'Item', 'JMenuModel' );
		$view =& $this->getView( 'Item', 'JMenuView' );
		$view->setModel( $model, true );

		// Set the layout and display
		$view->setLayout('type');
		$view->type();
	}

	/**
	 * Edits a menu item
	 */
	function edit()
	{
		$model	=& $this->getModel( 'Item', 'JMenuModel' );
		$view =& $this->getView( 'Item', 'JMenuView' );
		$view->setModel( $model, true );

		// Set the layout and display
		$view->setLayout('form');
		$view->edit();
	}

	/**
	 * Saves a menu item
	 */
	function save()
	{
		$cache = & JFactory::getCache('com_content');
		$cache->cleanCache();

		$model =& $this->getModel( 'Item', 'JMenuModel' );
		if ($model->store()) {
			$msg = JText::_( 'Menu item Saved' );
		} else {
			$msg = JText::_( 'Error Saving Menu item' );
		}

		$item =& $model->getItem();
		switch ( $this->_task ) {
			case 'apply':
				$this->setRedirect( 'index.php?option=com_menus&menutype='.$item->menutype.'&task=edit&cid[]='.$item->id.'&hidemainmenu=1' , $msg );
				break;

			case 'save':
			default:
				$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$item->menutype, $msg );
				break;
		}
	}

	/**
	* Cancels an edit operation
	*/
	function cancelItem()
	{
		global $mainframe;
//		$menu =& JTable::getInstance('menu', $this->getDBO() );
//		$menu->bind( $_POST );
//		$menuid = JRequest::getVar( 'menuid', 0, 'post', 'int' );
//		if ( $menuid ) {
//			$menu->id = $menuid;
//		}
//		$menu->checkin();
		$menutype = $mainframe->getUserStateFromRequest( "com_menus.menutype", 'menutype', 'mainmenu' );
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menutype);
	}

	/**
	* Cancels an edit operation
	*/
	function cancel()
	{
		global $mainframe;
//		$menu =& JTable::getInstance('menu', $this->getDBO() );
//		$menu->bind( $_POST );
//		$menuid = JRequest::getVar( 'menuid', 0, 'post', 'int' );
//		if ( $menuid ) {
//			$menu->id = $menuid;
//		}
//		$menu->checkin();
		$menutype = $mainframe->getUserStateFromRequest( "com_menus.menutype", 'menutype', 'mainmenu' );
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menutype);
	}

	/**
	* Form for copying item(s) to a specific menu
	*/
	function copy()
	{
		$model	=& $this->getModel( 'List', 'JMenuModel' );
		$view =& $this->getView( 'List', 'JMenuView' );
		$view->setModel( $model, true );
		$view->copyForm();
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function doCopy()
	{
		// Get some variables from the request
		$menu	= JRequest::getVar( 'menu', '', 'post' );
		$cid	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		$model	=& $this->getModel( 'List', 'JMenuModel' );

		if ($model->copy($cid, $menu)) {
			$msg = sprintf( JText::_( 'Menu Items Copied to' ), count( $cid ), $menu );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menu, $msg );
	}

	/**
	* Form for moving item(s) to a specific menu
	*/
	function move()
	{
		$model	=& $this->getModel( 'List', 'JMenuModel' );
		$view =& $this->getView( 'List', 'JMenuView' );
		$view->setModel( $model, true );
		$view->moveForm();
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function doMove()
	{
		// Get some variables from the request
		$menu	= JRequest::getVar( 'menu', '', 'post' );
		$cid	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		$model	=& $this->getModel( 'List', 'JMenuModel' );

		if ($model->move($cid, $menu)) {
			$msg = sprintf( JText::_( 'Menu Items Moved to' ), count( $cid ), $menu );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menu, $msg );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function publish()
	{
		// Get some variables from the request
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$menutype	= JRequest::getVar('menutype');

		$model =& $this->getModel( 'List', 'JMenuModel' );
		if ($model->setState($cid, 1)) {
			$msg = sprintf( JText::_( 'Menu Items Published' ), count( $cid ) );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menutype, $msg );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function unpublish()
	{
		// Get some variables from the request
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$menutype	= JRequest::getVar('menutype');

		$model =& $this->getModel( 'List', 'JMenuModel' );
		if ($model->setState($cid, 0)) {
			$msg = sprintf( JText::_( 'Menu Items Unpublished' ), count( $cid ) );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menutype, $msg );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function orderup()
	{
		$menutype	= JRequest::getVar('menutype');
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		if ($cid[0]) {
			$id = $cid[0];
		} else {
			$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menutype, JText::_('No Items Selected') );
			return false;
		}

		$model =& $this->getModel( 'List', 'JMenuModel' );
		if ($model->orderItem($id, -1)) {
			$msg = JText::_( 'Menu Item Moved Up' );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menutype, $msg );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function orderdown()
	{
		$menutype	= JRequest::getVar('menutype');
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		if ($cid[0]) {
			$id = $cid[0];
		} else {
			$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menutype, JText::_('No Items Selected') );
			return false;
		}

		$model =& $this->getModel( 'List', 'JMenuModel' );
		if ($model->orderItem($id, 1)) {
			$msg = JText::_( 'Menu Item Moved Down' );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menutype, $msg );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function saveorder()
	{
		$cid		= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$menutype	= JRequest::getVar('menutype');

		$model =& $this->getModel( 'List', 'JMenuModel' );
		if ($model->setOrder($cid, $menutype)) {
			$msg = JText::_( 'New ordering saved' );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menutype, $msg );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function accesspublic()
	{
		// Get some variables from the request
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$menutype	= JRequest::getVar('menutype');

		$model =& $this->getModel( 'List', 'JMenuModel' );
		if ($model->setAccess($cid, 0)) {
			$msg = sprintf( JText::_( 'Menu Items Set Public' ), count( $cid ) );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menutype, $msg );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function accessregistered()
	{
		// Get some variables from the request
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$menutype	= JRequest::getVar('menutype');

		$model =& $this->getModel( 'List', 'JMenuModel' );
		if ($model->setAccess($cid, 1)) {
			$msg = sprintf( JText::_( 'Menu Items Set Registered' ), count( $cid ) );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menutype, $msg );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function accessspecial()
	{
		// Get some variables from the request
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$menutype	= JRequest::getVar('menutype');

		$model =& $this->getModel( 'List', 'JMenuModel' );
		if ($model->setAccess($cid, 2)) {
			$msg = sprintf( JText::_( 'Menu Items Set Special' ), count( $cid ) );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menutype, $msg );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function setdefault()
	{
		// Get some variables from the request
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$menutype	= JRequest::getVar('menutype');

		$model =& $this->getModel( 'List', 'JMenuModel' );
		if ($model->setHome($cid[0])) {
			$msg = JText::_( 'Default Menu Item Set' );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menutype, $msg );
	}

	function remove()
	{
		// Get some variables from the request
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$menutype	= JRequest::getVar('menutype');

		$model =& $this->getModel( 'List', 'JMenuModel' );
		if ($model->toTrash($cid, $menutype)) {
			$msg = sprintf( JText::_( 'Item(s) sent to the Trash' ), count( $cid ) );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menutype, $msg );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function view()
	{
		$model	=& $this->getModel( 'List', 'JMenuModel' );
		$view =& $this->getView( 'List', 'JMenuView' );
		$view->setModel( $model, true );
		$view->display();
	}


	/**
	 * Controller for view listing menu types and related statical info
	 * @param string The URL option
	 */
	function viewMenus()
	{
		$view =& $this->getView( 'Menus', 'JMenuView' );
		$model	=& $this->getModel( 'Menutype', 'JMenuModel' );
		$view->setModel( $model, true );
		$view->display();
	}


	/**
	 * Controller for view to create or edit a menu type
	 */
	function editMenu()
	{
		$view =& $this->getView( 'Menus', 'JMenuView' );
		$model	=& $this->getModel( 'Menutype', 'JMenuModel' );
		$view->setModel( $model, true );
		$view->editForm();
	}

	/**
	 * Controller for saving a menu type
	 */
	function saveMenu()
	{
		$db		=& JFactory::getDBO();
		$id		= JRequest::getVar( 'id', 0, '', 'int' );

		$oldType =& JTable::getInstance('menutypes', $db, 'JTable');
		$oldType->load( $id );

		$menuType =& JTable::getInstance('menutypes', $db, 'JTable');
		$menuType->bind( JRequest::get( 'post' ) );

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

		if ($isNew)
		{
			$title = JRequest::getVar( 'module_title', $menuType->menutype, 'post' );

			$module =& JTable::getInstance( 'module', $db );
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
			$db->setQuery( $query );
			if ( !$db->query() ) {
				josErrorAlert( $db->getErrorMsg() );
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
			$db->setQuery( $query );
			$modules = $db->loadResultArray();

			foreach ($modules as $id) {
				$row =& JTable::getInstance('module', $db );
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
			$db->setQuery( $query );
			$db->query();

			$msg = JText::_( 'Menu Items & Modules updated' );
		}

		$this->setRedirect( 'index.php?option=com_menus', $msg );
	}

	/**
	 * Controller for a view to confirm the deletion of a menu type
	 */
	function deleteMenu()
	{
		$view =& $this->getView( 'Menus', 'JMenuView' );
		$model	=& $this->getModel( 'Menutype', 'JMenuModel' );
		$view->setModel( $model, true );
		$view->deleteForm();
	}

	/**
	 * Delete a menu
	 */
	function doDeleteMenu()
	{
		$id = (int) JRequest::getVar( 'id', 0 );
		if ($id <= 0) {
			JError::raiseError( 500, JText::_( 'Invalid ID provided' ) );
			return false;
		}

		$model =& $this->getModel( 'Menutype', 'JMenuModel' );
		if (!$model->canDelete()) {
			JError::raiseError( 500, $model->getError() );
			return false;
		}
		$err = null;
		if (!$model->delete()) {
			 $err = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus', $err );
	}

	/**
	* Compiles a list of the articles you have selected to Copy
	*/
	function copyMenu()
	{
		$view =& $this->getView( 'Menus', 'JMenuView' );
		$model	=& $this->getModel( 'Menutype', 'JMenuModel' );
		$view->setModel( $model, true );
		$view->copyForm();
	}

	/**
	* Copies a complete menu, all its items and creates a new module, using the name speified
	*/
	function doCopyMenu()
	{
		global $mainframe;

		$db				=& JFactory::getDBO();
		$type	 		= JRequest::getVar( 'type', null, 'post' );
		$menu_name 		= JRequest::getVar( 'menu_name', 'New Menu', 'post' );
		$module_name 	= JRequest::getVar( 'module_name', 'New Module', 'post' );

		// check for unique menutype for new menu copy
		$query = "SELECT params" .
				"\n FROM #__modules" .
				"\n WHERE module = 'mod_mainmenu'";
		$db->setQuery( $query );
		$menus = $db->loadResultArray();
		foreach ( $menus as $menu ) {
			$params = new JParameter( $menu );
			if ( $params->get('menutype') == $menu_name ) {
				JError::raiseError( 500, JText::_( 'ERRORMENUNAMEEXISTS' ) );
				exit;
			}
		}

		// copy the menu items
		$mids 		= JRequest::getVar( 'mids', array(), 'post', 'array' );
		$total 		= count( $mids );
		$copy 		=& JTable::getInstance('menu', $db );
		$original 	=& JTable::getInstance('menu', $db );
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
		$row =& JTable::getInstance('module', $db );
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
		$query = "INSERT INTO #__modules_menu" .
				"\n VALUES ( ".$row->id.", 0 )";
		$db->setQuery( $query );
		if ( !$db->query() ) {
			echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
			exit();
		}

		// Insert the menu type
		$query = "INSERT INTO `#__menu_types`  ( `menutype` , `title` , `description` ) " .
				"\n VALUES ( ".$db->Quote($menu_name).", ".$db->Quote(JText::_('New Menu')).", '')";
		$db->setQuery( $query );
		if ( !$db->query() ) {
			echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
			exit();
		}

		$msg = sprintf( JText::_( 'Copy of Menu created' ), $type, $total );
		$mainframe->redirect( 'index.php?option=com_menus', $msg );
	}
}
?>