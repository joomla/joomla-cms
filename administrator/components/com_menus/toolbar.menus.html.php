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

		JMenuBar::startTable();
		JMenuBar::title(  JText::_( 'New Menu Item' ), 'menu.png' );
		JMenuBar::customX( 'edit', 'next.png', 'next_f2.png', JText::_( 'Next' ), true );
		JMenuBar::spacer();
		JMenuBar::cancel();
		JMenuBar::spacer();
		JMenuBar::help( 'screen.menus.new' );
		JMenuBar::endTable();
	}

	/**
	* Draws the menu to Move Menut Items
	*/
	function _MOVEMENU()	{

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Move Menu Items' ) );
		JMenuBar::custom( 'movemenusave', 'move.png', 'move_f2.png', JText::_( 'Move' ), false );
		JMenuBar::spacer();
		JMenuBar::cancel( 'cancelmovemenu' );
		JMenuBar::spacer();
		JMenuBar::help( 'screen.menus.move' );
		JMenuBar::endTable();
	}

	/**
	* Draws the menu to Move Menut Items
	*/
	function _COPYMENU()	{

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Copy Menu Items' ) );
		JMenuBar::custom( 'copymenusave', 'copy.png', 'copy_f2.png', JText::_( 'Copy' ), false );
		JMenuBar::spacer();
		JMenuBar::cancel( 'cancelcopymenu' );
		JMenuBar::spacer();
		JMenuBar::help( 'screen.menus.copy' );
		JMenuBar::endTable();
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

		JMenuBar::startTable();
		if ( !$id ) {
			$link = 'index2.php?option=com_menus&menutype='. $menutype .'&task=new&hidemainmenu=1';
			JMenuBar::back( 'Back', $link );
			JMenuBar::spacer();
		}
		JMenuBar::save();
		JMenuBar::spacer();
		JMenuBar::apply();
		JMenuBar::spacer();
		if ( $id ) {
			// for existing content items the button is renamed `close`
			JMenuBar::cancel( 'cancel', 'Close' );
		} else {
			JMenuBar::cancel();
		}
		JMenuBar::spacer();
		JMenuBar::help( 'screen.menus.edit' );
		JMenuBar::endTable();
	}

	function _DEFAULT() {
		global $menutype;

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Menu Manager' ) .'<small><small>['.$menutype.']</small></small>', 'menu.png' );
		JMenuBar::publishList();
		JMenuBar::spacer();
		JMenuBar::unpublishList();
		JMenuBar::spacer();
		JMenuBar::customX( 'movemenu', 'move.png', 'move_f2.png', JText::_( 'Move' ), true );
		JMenuBar::spacer();
		JMenuBar::customX( 'copymenu', 'copy.png', 'copy_f2.png', JText::_( 'Copy' ), true );
		JMenuBar::spacer();
		JMenuBar::trash();
		JMenuBar::spacer();
		JMenuBar::editListX();
		JMenuBar::spacer();
		JMenuBar::addNewX();
		JMenuBar::spacer();
		JMenuBar::help( 'screen.menus' );
		JMenuBar::endTable();
	}
}
?>