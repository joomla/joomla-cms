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

jimport('joomla.application.controller');

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
				$this->setRedirect( 'index.php?option=com_menus&menutype='.$item->menutype, $msg );
				break;
		}
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
		$this->setRedirect( 'index.php?option=com_menus&menutype='.$menutype);
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
		$this->setRedirect( 'index.php?option=com_menus&menutype='.$menu, $msg );
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
		$this->setRedirect( 'index.php?option=com_menus&menutype='.$menu, $msg );
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
		$this->setRedirect( 'index.php?option=com_menus&menutype='.$menutype, $msg );
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
		$this->setRedirect( 'index.php?option=com_menus&menutype='.$menutype, $msg );
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
			$this->setRedirect( 'index.php?option=com_menus&menutype='.$menutype, JText::_('No Items Selected') );
			return false;
		}

		$model =& $this->getModel( 'List', 'JMenuModel' );
		if ($model->orderItem($id, -1)) {
			$msg = JText::_( 'Menu Item Moved Up' );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&menutype='.$menutype, $msg );
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
			$this->setRedirect( 'index.php?option=com_menus&menutype='.$menutype, JText::_('No Items Selected') );
			return false;
		}

		$model =& $this->getModel( 'List', 'JMenuModel' );
		if ($model->orderItem($id, 1)) {
			$msg = JText::_( 'Menu Item Moved Down' );
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_menus&menutype='.$menutype, $msg );
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
		$this->setRedirect( 'index.php?option=com_menus&menutype='.$menutype, $msg );
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
		$this->setRedirect( 'index.php?option=com_menus&menutype='.$menutype, $msg );
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
		$this->setRedirect( 'index.php?option=com_menus&menutype='.$menutype, $msg );
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
		$this->setRedirect( 'index.php?option=com_menus&menutype='.$menutype, $msg );
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
		$this->setRedirect( 'index.php?option=com_menus&menutype='.$menutype, $msg );
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
		$this->setRedirect( 'index.php?option=com_menus&menutype='.$menutype, $msg );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function viewList()
	{
		$model	=& $this->getModel( 'List', 'JMenuModel' );
		$view =& $this->getView( 'List', 'JMenuView' );
		$view->setModel( $model, true );
		$view->display();
	}
}
?>