<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Menus
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Menus
*/
class TOOLBAR_menus {
	/**
	* Draws the menu for a New top menu item
	*/
	function _NEW()	{

		JMenuBar::title(  JText::_( 'New Menu Item' ), 'menu.png' );
		JMenuBar::customX( 'edit', 'next.png', 'next_f2.png', 'Next', true );
		JMenuBar::cancel();
		JMenuBar::help( 'screen.menus.new' );
	}

	/**
	* Draws the menu to Move Menut Items
	*/
	function _MOVEMENU()	{

		JMenuBar::title( JText::_( 'Move Menu Items' ) );
		JMenuBar::custom( 'doMove', 'move.png', 'move_f2.png', 'Move', false );
		JMenuBar::cancel();
		JMenuBar::help( 'screen.menus.move' );
	}

	/**
	* Draws the menu to Move Menut Items
	*/
	function _COPYMENU()	{

		JMenuBar::title( JText::_( 'Copy Menu Items' ) );
		JMenuBar::custom( 'doCopy', 'copy.png', 'copy_f2.png', 'Copy', false );
		JMenuBar::cancel();
		JMenuBar::help( 'screen.menus.copy' );
	}

	/**
	* Draws the menu to edit a menu item
	*/
	function _EDIT() {

		$cid = JRequest::getVar( 'cid', array(0), '', 'array' );
		$id = $cid[0];
		$menutype	= JRequest::getVar( 'menutype', 'mainmenu' );

		if ( !$id ) {
			JMenuBar::title( JText::_( 'New Menu Item' ), 'menu.png' );
		} else {
			JMenuBar::title( JText::_( 'Edit Menu Item' ), 'menu.png' );
		}

		JMenuBar::save();
		JMenuBar::apply();

		if ( $id ) {
			// for existing items the button is renamed `close`
			JMenuBar::cancel( 'cancel', 'Close' );
		} else {
			JMenuBar::cancel();
		}
		JMenuBar::help( 'screen.menus.edit' );
	}

	function _DEFAULT() {
		// Get the toolbar object instance
		$bar = & JToolBar::getInstance('JComponent');

		$menutype 	= JRequest::getVar( 'menutype', 'mainmenu' );

		JMenuBar::title( JText::_( 'Menu Manager' ) .': <small><small>['.$menutype.']</small></small>', 'menu.png' );
		JMenuBar::makeDefault( 'setdefault' );
		JMenuBar::publishList();
		JMenuBar::unpublishList();
		JMenuBar::customX( 'move', 'move.png', 'move_f2.png', 'Move', true );
		JMenuBar::customX( 'copy', 'copy.png', 'copy_f2.png', 'Copy', true );
		JMenuBar::trash();
		JMenuBar::editListX();
		JMenuBar::addNewX('type');

		// Add a popup configuration button
		//$bar->appendButton( 'Popup', 'new', 'New', 'index3.php?option=com_menus&task=wizard&menutype='.$menutype, '700', '500' );

		JMenuBar::help( 'screen.menus' );
	}
}
?>