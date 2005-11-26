<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Menus
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Menus
*/
class TOOLBAR_menus {
	/**
	* Draws the menu for a New top menu item
	*/
	function _NEW()	{
		
		mosMenuBar::startTable();
		mosMenuBar::title(  JText::_( 'New Menu Item' ), 'menu.png' );
		mosMenuBar::customX( 'edit', 'next.png', 'next_f2.png', JText::_( 'Next' ), true );
		mosMenuBar::spacer();
		mosMenuBar::cancel();
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.menus.new' );
		mosMenuBar::endTable();
	}

	/**
	* Draws the menu to Move Menut Items
	*/
	function _MOVEMENU()	{
		
		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Move Menu Items' ) );
		mosMenuBar::custom( 'movemenusave', 'move.png', 'move_f2.png', JText::_( 'Move' ), false );
		mosMenuBar::spacer();
		mosMenuBar::cancel( 'cancelmovemenu' );
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.menus.move' );
		mosMenuBar::endTable();
	}

	/**
	* Draws the menu to Move Menut Items
	*/
	function _COPYMENU()	{
		
		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Copy Menu Items' ) );
		mosMenuBar::custom( 'copymenusave', 'copy.png', 'copy_f2.png', JText::_( 'Copy' ), false );
		mosMenuBar::spacer();
		mosMenuBar::cancel( 'cancelcopymenu' );
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.menus.copy' );
		mosMenuBar::endTable();
	}

	/**
	* Draws the menu to edit a menu item
	*/
	function _EDIT() {
		global $id;

		if ( !$id ) {
			$cid = mosGetParam( $_POST, 'cid', array(0) );
			$id = $cid[0];
		}
		$menutype 	= mosGetParam( $_REQUEST, 'menutype', 'mainmenu' );

		mosMenuBar::startTable();
		if ( !$id ) {
			$link = 'index2.php?option=com_menus&menutype='. $menutype .'&task=new&hidemainmenu=1';
			mosMenuBar::back( 'Back', $link );
			mosMenuBar::spacer();
		}
		mosMenuBar::save();
		mosMenuBar::spacer();
		mosMenuBar::apply();
		mosMenuBar::spacer();
		if ( $id ) {
			// for existing content items the button is renamed `close`
			mosMenuBar::cancel( 'cancel', 'Close' );
		} else {
			mosMenuBar::cancel();
		}
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.menus.edit' );
		mosMenuBar::endTable();
	}

	function _DEFAULT() {
		global $menutype;
		
		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Menu Manager' ) .'<small><small>['.$menutype.']</small></small>', 'menu.png' );
		mosMenuBar::publishList();
		mosMenuBar::spacer();
		mosMenuBar::unpublishList();
		mosMenuBar::spacer();
		mosMenuBar::customX( 'movemenu', 'move.png', 'move_f2.png', JText::_( 'Move' ), true );
		mosMenuBar::spacer();
		mosMenuBar::customX( 'copymenu', 'copy.png', 'copy_f2.png', JText::_( 'Copy' ), true );
		mosMenuBar::spacer();
		mosMenuBar::trash();
		mosMenuBar::spacer();
		mosMenuBar::editListX();
		mosMenuBar::spacer();
		mosMenuBar::addNewX();
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.menus' );
		mosMenuBar::endTable();
	}
}
?>