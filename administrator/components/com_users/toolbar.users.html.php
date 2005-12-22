<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Users
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
* @subpackage Users
*/
class TOOLBAR_users {
	/**
	* Draws the menu to edit a user
	*/
	function _EDIT() {
		global $id;

		$text = $id ? JText::_( 'Edit' ) : JText::_( 'Add' );

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'User' ) .':<small>'. $text .'</small>', 'user.png' );
		JMenuBar::save();
		JMenuBar::spacer();
		JMenuBar::apply();
		JMenuBar::spacer();
		if ( $id ) {
			// for existing content items the button is renamed `close`
			JMenuBar::cancel( 'cancel', JText::_( 'Close' ) );
		} else {
			JMenuBar::cancel();
		}
		JMenuBar::spacer();
		JMenuBar::help( 'screen.users.edit' );
		JMenuBar::endTable();
	}

	function _DEFAULT() {

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'User Manager' ), 'user.png' );
		JMenuBar::custom( 'logout', 'cancel.png', 'cancel_f2.png', '&nbsp;'. JText::_( 'Logout' ) );
		JMenuBar::spacer();
		JMenuBar::deleteList();
		JMenuBar::spacer();
		JMenuBar::editListX();
		JMenuBar::spacer();
		JMenuBar::addNewX();
		JMenuBar::spacer();
		JMenuBar::help( 'screen.users' );
		JMenuBar::endTable();
	}
}
?>