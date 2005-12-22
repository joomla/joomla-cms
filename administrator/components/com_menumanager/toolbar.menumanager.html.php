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
class TOOLBAR_menumanager {
	/**
	* Draws the menu for the Menu Manager
	*/
	function _DEFAULT() {

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Menu Manager' ), 'menu.png' );
		JMenuBar::customX( 'copyconfirm', 'copy.png', 'copy_f2.png', JText::_( 'Copy' ), true );
		JMenuBar::spacer();
		JMenuBar::customX( 'deleteconfirm', 'delete.png', 'delete_f2.png', JText::_( 'Delete' ), true );
		JMenuBar::spacer();
		JMenuBar::editListX();
		JMenuBar::spacer();
		JMenuBar::addNewX();
		JMenuBar::spacer();
		JMenuBar::help( 'screen.menumanager' );
		JMenuBar::endTable();
	}

	/**
	* Draws the menu to delete a menu
	*/
	function _DELETE() {
		JMenuBar::startTable();
		JMenuBar::cancel( );
		JMenuBar::endTable();
	}

	/**
	* Draws the menu to create a New menu
	*/
	function _NEWMENU()	{

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Menu Details' ), 'menu.png' );
		JMenuBar::custom( 'savemenu', 'save.png', 'save_f2.png', JText::_( 'Save' ), false );
		JMenuBar::spacer();
		JMenuBar::cancel();
		JMenuBar::spacer();
		JMenuBar::help( 'screen.menumanager.new' );
		JMenuBar::endTable();
	}

	/**
	* Draws the menu to create a New menu
	*/
	function _COPYMENU()	{

		JMenuBar::startTable();
		JMenuBar::title(  JText::_( 'Copy Menu Items' ) );
		JMenuBar::custom( 'copymenu', 'copy.png', 'copy_f2.png', JText::_( 'Copy' ), false );
		JMenuBar::spacer();
		JMenuBar::cancel();
		JMenuBar::spacer();
		JMenuBar::help( 'screen.menumanager.copy' );
		JMenuBar::endTable();
	}

}
?>