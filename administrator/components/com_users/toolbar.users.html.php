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

		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'User' ) .':<small>'. $text .'</small>', 'user.png' );
		mosMenuBar::save();
		mosMenuBar::spacer();
		mosMenuBar::apply();
		mosMenuBar::spacer();
		if ( $id ) {
			// for existing content items the button is renamed `close`
			mosMenuBar::cancel( 'cancel', JText::_( 'Close' ) );
		} else {
			mosMenuBar::cancel();
		}
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.users.edit' );
		mosMenuBar::endTable();
	}

	function _DEFAULT() {

		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'User Manager' ), 'user.png' );
		mosMenuBar::custom( 'logout', 'cancel.png', 'cancel_f2.png', '&nbsp;'. JText::_( 'Logout' ) );
		mosMenuBar::spacer();
		mosMenuBar::deleteList();
		mosMenuBar::spacer();
		mosMenuBar::editListX();
		mosMenuBar::spacer();
		mosMenuBar::addNewX();
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.users' );
		mosMenuBar::endTable();
	}
}
?>