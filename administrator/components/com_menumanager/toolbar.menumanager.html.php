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

		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Menu Manager' ), 'menu.png' );
		mosMenuBar::customX( 'copyconfirm', 'copy.png', 'copy_f2.png', JText::_( 'Copy' ), true );
		mosMenuBar::spacer();
		mosMenuBar::customX( 'deleteconfirm', 'delete.png', 'delete_f2.png', JText::_( 'Delete' ), true );
		mosMenuBar::spacer();
		mosMenuBar::editListX();
		mosMenuBar::spacer();
		mosMenuBar::addNewX();
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.menumanager' );
		mosMenuBar::endTable();
	}

	/**
	* Draws the menu to delete a menu
	*/
	function _DELETE() {
		mosMenuBar::startTable();
		mosMenuBar::cancel( );
		mosMenuBar::endTable();
	}

	/**
	* Draws the menu to create a New menu
	*/
	function _NEWMENU()	{

		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Menu Details' ), 'menu.png' );
		mosMenuBar::custom( 'savemenu', 'save.png', 'save_f2.png', JText::_( 'Save' ), false );
		mosMenuBar::spacer();
		mosMenuBar::cancel();
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.menumanager.new' );
		mosMenuBar::endTable();
	}

	/**
	* Draws the menu to create a New menu
	*/
	function _COPYMENU()	{

		mosMenuBar::startTable();
		mosMenuBar::title(  JText::_( 'Copy Menu Items' ) );
		mosMenuBar::custom( 'copymenu', 'copy.png', 'copy_f2.png', JText::_( 'Copy' ), false );
		mosMenuBar::spacer();
		mosMenuBar::cancel();
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.menumanager.copy' );
		mosMenuBar::endTable();
	}

}
?>