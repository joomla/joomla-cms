<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights
 * reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * @package		Joomla
 * @subpackage	Menus
 */
class MenusController extends JController
{
	/**
	 * New menu item wizard
	 */
	function type()
	{
		$model	=& $this->getModel( 'Item' );
		$view =& $this->getView( 'Item' );
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
		$model	=& $this->getModel( 'Item' );
		$model->checkout();
		
		$view =& $this->getView( 'Item' );
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
		$cache->clean();

		$model	=& $this->getModel( 'Item' );
		$post	= JRequest::get('post');
		// allow name only to contain html
		$post['name'] = JRequest::getVar( 'name', '', 'post', 'string', JREQUEST_ALLOWHTML );
		$model->setState( 'request', $post );

		if ($model->store()) {
			$msg = JText::_( 'Menu item Saved' );
		} else {
			$msg = JText::_( 'Error Saving Menu item' );
		}

		$item =& $model->getItem();
		switch ( $this->_task ) {
			case 'apply':
				$this->setRedirect( 'index.php?option=com_menus&menutype='.$item->menutype.'&task=edit&cid[]='.$item->id.'' , $msg );
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
		$menutype = $mainframe->getUserStateFromRequest( 'com_menus.menutype', 'menutype', 'mainmenu', 'string' );
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menutype);
	}

	/**
	* Cancels an edit operation
	*/
	function cancel()
	{
		$this->setRedirect( 'index.php?option=com_menus');
	}

	/**
	* Form for copying item(s) to a specific menu
	*/
	function copy()
	{
		$model	=& $this->getModel( 'List' );
		$view =& $this->getView( 'List' );
		$view->setModel( $model, true );
		$view->copyForm();
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function doCopy()
	{
		// Get some variables from the request
		$menu	= JRequest::getVar( 'menu', '', 'post', 'string' );
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$model	=& $this->getModel( 'List' );

		if ($model->copy($cid, $menu)) {
			$msg = JText::sprintf( 'Menu Items Copied to', count( $cid ), $menu );
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
		$model	=& $this->getModel( 'List');
		$view =& $this->getView( 'List' );
		$view->setModel( $model, true );
		$view->moveForm();
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function doMove()
	{
		// Get some variables from the request
		$menu	= JRequest::getVar( 'menu', '', 'post', 'string' );
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$model	=& $this->getModel( 'List' );

		if ($model->move($cid, $menu)) {
			$msg = JText::sprintf( 'Menu Items Moved to', count( $cid ), $menu );
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
		$menu	= JRequest::getVar( 'menutype', '', 'post', 'string' );
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$model =& $this->getModel( 'List' );
		if ($model->setItemState($cid, 1)) {
			$msg = JText::sprintf( 'Menu Items Published', count( $cid ) );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menu, $msg );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function unpublish()
	{
		// Get some variables from the request
		$menu	= JRequest::getVar( 'menutype', '', 'post', 'string' );
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$model =& $this->getModel( 'List' );
		if ($model->setItemState($cid, 0)) {
			$msg = JText::sprintf( 'Menu Items Unpublished', count( $cid ) );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menu, $msg );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function orderup()
	{
		$menu	= JRequest::getVar( 'menutype', '', 'post', 'string' );
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (isset($cid[0]) && $cid[0]) {
			$id = $cid[0];
		} else {
			$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menu, JText::_('No Items Selected') );
			return false;
		}

		$model =& $this->getModel( 'List' );
		if ($model->orderItem($id, -1)) {
			$msg = JText::_( 'Menu Item Moved Up' );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menu, $msg );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function orderdown()
	{
		$menu	= JRequest::getVar( 'menutype', '', 'post', 'string' );
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (isset($cid[0]) && $cid[0]) {
			$id = $cid[0];
		} else {
			$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menu, JText::_('No Items Selected') );
			return false;
		}

		$model =& $this->getModel( 'List' );
		if ($model->orderItem($id, 1)) {
			$msg = JText::_( 'Menu Item Moved Down' );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menu, $msg );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function saveorder()
	{
		$menu	= JRequest::getVar( 'menutype', '', 'post', 'string' );
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$model =& $this->getModel( 'List' );
		if ($model->setOrder($cid, $menu)) {
			$msg = JText::_( 'New ordering saved' );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menu, $msg );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function accesspublic()
	{
		// Get some variables from the request
		$menu	= JRequest::getVar( 'menutype', '', 'post', 'string' );
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$model =& $this->getModel( 'List' );
		if ($model->setAccess($cid, 0)) {
			$msg = JText::sprintf( 'Menu Items Set Public', count( $cid ) );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menu, $msg );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function accessregistered()
	{
		// Get some variables from the request
		$menu	= JRequest::getVar( 'menutype', '', 'post', 'string' );
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$model =& $this->getModel( 'List' );
		if ($model->setAccess($cid, 1)) {
			$msg = JText::sprintf( 'Menu Items Set Registered', count( $cid ) );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menu, $msg );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function accessspecial()
	{
		// Get some variables from the request
		$menu	= JRequest::getVar( 'menutype', '', 'post', 'string' );
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$model =& $this->getModel( 'List' );
		if ($model->setAccess($cid, 2)) {
			$msg = JText::sprintf( 'Menu Items Set Special', count( $cid ) );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menu, $msg );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function setdefault()
	{
		// Get some variables from the request
		$menu	= JRequest::getVar( 'menutype', '', 'post', 'string' );
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (isset($cid[0]) && $cid[0]) {
			$id = $cid[0];
		} else {
			$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menu, JText::_('No Items Selected') );
			return false;
		}

		$model =& $this->getModel( 'List' );
		if ($model->setHome($id)) {
			$msg = JText::_( 'Default Menu Item Set' );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menu, $msg );
	}

	function remove()
	{
		// Get some variables from the request
		$menu	= JRequest::getVar( 'menutype', '', 'post', 'string' );
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (!count($cid)) {
			$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menu, JText::_('No Items Selected') );
			return false;
		}

		$model =& $this->getModel( 'List' );
		if ($n = $model->toTrash($cid)) {
			$msg = JText::sprintf( 'Item(s) sent to the Trash', $n );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&task=view&menutype='.$menu, $msg );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function view()
	{
		$model	=& $this->getModel( 'List' );
		$view =& $this->getView( 'List' );
		$view->setModel( $model, true );
		$view->display();
	}


	/**
	 * Controller for view listing menu types and related statical info
	 * @param string The URL option
	 */
	function viewMenus()
	{

		$view =& $this->getView( 'Menus');
		$model	=& $this->getModel( 'Menutype' );
		$view->setModel( $model, true );
		$view->display();
	}


	/**
	 * Controller for view to create or edit a menu type
	 */
	function editMenu()
	{
		$view =& $this->getView( 'Menus' );
		$model	=& $this->getModel( 'Menutype' );
		$view->setModel( $model, true );
		$view->editForm();
	}

	/**
	 * Controller for saving a menu type
	 */
	function saveMenu()
	{
		global $mainframe;

		$db		=& JFactory::getDBO();
		$id		= JRequest::getVar( 'id', 0, 'post', 'int' );

		$oldType =& JTable::getInstance('menutypes' );
		$oldType->load( $id );

		$menuType =& JTable::getInstance('menutypes');
		$menuType->bind( JRequest::get( 'post' ) );

		$isNew		= ($menuType->id == 0);
		$isChanged	= ($oldType->menutype != $menuType->menutype);

		if (!$menuType->check()) {
			return JError::raiseWarning( 500, $row->getError() );
		}

		if (!$menuType->store()) {
			return JError::raiseWarning( 500, $row->getError() );
		}

		if ($isNew)
		{
			if ($title = JRequest::getVar( 'module_title', $menuType->menutype, 'post', 'string' ))
			{
				$module =& JTable::getInstance( 'module');
				$module->title 		= $title;
				$module->position 	= 'left';
				$module->module 	= 'mod_mainmenu';
				$module->published	= 0;
				$module->iscore 	= 0;
				$module->params		= 'menutype='. $menuType->menutype;

				// check then store data in db
				if (!$module->check()) {
					return JError::raiseWarning( 500, $row->getError() );
				}
				if (!$module->store()) {
					return JError::raiseWarning( 500, $row->getError() );
				}
				$module->checkin();
				$module->reorder( 'position='.$db->Quote($module->position) );

				// module assigned to show on All pages by default
				// Clean up possible garbage first
				$query = 'DELETE FROM #__modules_menu WHERE moduleid = '.(int) $module->id;
				$db->setQuery( $query );
				if (!$db->query()) {
					return JError::raiseWarning( 500, $row->getError() );
				}

				// ToDO: Changed to become a Joomla! db-object
				$query = 'INSERT INTO #__modules_menu VALUES ( '.(int) $module->id.', 0 )';
				$db->setQuery( $query );
				if (!$db->query()) {
					return JError::raiseWarning( 500, $row->getError() );
				}
			}

			$msg = JText::sprintf( 'New Menu created', $menuType->menutype );
		}
		else if ($isChanged)
		{
			$oldTerm = $oldType->menutype;
			$newTerm = $menuType->menutype;

			// change menutype being of all mod_mainmenu modules calling old menutype
			$query = 'SELECT id'
			. ' FROM #__modules'
			. ' WHERE module = "mod_mainmenu"'
			. ' AND params LIKE "%menutype='.$db->getEscaped($oldTerm).'%"'
			;
			$db->setQuery( $query );
			$modules = $db->loadResultArray();

			foreach ($modules as $id)
			{
				$row =& JTable::getInstance('module');
				$row->load( $id );

				$row->params = str_replace( $oldTerm, $newTerm, $row->params );

				// check then store data in db
				if ( !$row->check() ) {
					return JError::raiseWarning( 500, $row->getError() );
				}
				if ( !$row->store() ) {
					return JError::raiseWarning( 500, $row->getError() );
				}
				$row->checkin();
			}

			// change menutype of all menuitems using old menutype
			$query = 'UPDATE #__menu'
			. ' SET menutype = '.$db->Quote($newTerm)
			. ' WHERE menutype = '.$db->Quote($oldTerm)
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
		$view =& $this->getView( 'Menus' );
		$model	=& $this->getModel( 'Menutype' );
		$view->setModel( $model, true );
		$view->deleteForm();
	}

	/**
	 * Delete a menu
	 */
	function doDeleteMenu()
	{
		$id = JRequest::getVar( 'id', 0, '', 'int' );
		if ($id <= 0) {
			JError::raiseError( 500, JText::_( 'Invalid ID provided' ) );
			return false;
		}

		$model =& $this->getModel( 'Menutype' );
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
		$view =& $this->getView( 'Menus' );
		$model	=& $this->getModel( 'Menutype' );
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
		$type			= JRequest::getVar( 'type', '', 'post', 'string' );
		$menu_name		= JRequest::getVar( 'menu_name', 'New Menu', 'post', 'string' );
		$module_name	= JRequest::getVar( 'module_name', 'New Module', 'post', 'string' );

		// check for unique menutype for new menu copy
		$query = 'SELECT params' .
				' FROM #__modules' .
				' WHERE module = "mod_mainmenu"';
		$db->setQuery( $query );
		$menus = $db->loadResultArray();

		foreach ( $menus as $menu )
		{
			$params = new JParameter( $menu );
			if ( $params->get('menutype') == $menu_name ) {
				JError::raiseError( 500, JText::_( 'ERRORMENUNAMEEXISTS' ) );
				$mainframe->close();
			}
		}

		// copy the menu items
		$mids 		= JRequest::getVar( 'mids', array(), 'post', 'array' );
		JArrayHelper::toInteger($mids);
		$total 		= count( $mids );
		$copy 		=& JTable::getInstance('menu');
		$original 	=& JTable::getInstance('menu');
		sort( $mids );
		$a_ids 		= array();

		foreach( $mids as $mid )
		{
			$original->load( $mid );
			$copy 			= $original;
			$copy->id 		= NULL;
			$copy->parent 	= $a_ids[$original->parent];
			$copy->menutype = $menu_name;

			if ( !$copy->check() ) {
				josErrorAlert( $copy->getError() );
				$mainframe->close();
			}
			if ( !$copy->store() ) {
				josErrorAlert( $copy->getError() );
				$mainframe->close();
			}
			$a_ids[$original->id] = $copy->id;
		}

		// create the module copy
		$row =& JTable::getInstance('module');
		$row->load( 0 );
		$row->title 	= $module_name;
		$row->iscore 	= 0;
		$row->published = 1;
		$row->position 	= 'left';
		$row->module 	= 'mod_mainmenu';
		$row->params 	= 'menutype='. $menu_name;

		if (!$row->check()) {
			josErrorAlert( $row->getError() );
			$mainframe->close();
		}
		if (!$row->store()) {
			josErrorAlert( $row->getError() );
			$mainframe->close();
		}
		$row->checkin();
		$row->reorder( 'position='.$db->Quote($row->position) );
		// module assigned to show on All pages by default
		// ToDO: Changed to become a Joomla! db-object
		$query = 'INSERT INTO #__modules_menu' .
				' VALUES ( '.(int) $row->id.', 0 )';
		$db->setQuery( $query );
		if ( !$db->query() ) {
			echo "<script> alert('".$db->getErrorMsg(true)."'); window.history.go(-1); </script>\n";
			$mainframe->close();
		}

		// Insert the menu type
		$query = 'INSERT INTO `#__menu_types`  ( `menutype` , `title` , `description` ) ' .
				' VALUES ( '.$db->Quote($menu_name).', '.$db->Quote(JText::_('New Menu')).', "")';
		$db->setQuery( $query );
		if ( !$db->query() ) {
			echo "<script> alert('".$db->getErrorMsg(true)."'); window.history.go(-1); </script>\n";
			$mainframe->close();
		}

		$msg = JText::sprintf( 'Copy of Menu created', $type, $total );
		$mainframe->redirect( 'index.php?option=com_menus', $msg );
	}
}
?>
