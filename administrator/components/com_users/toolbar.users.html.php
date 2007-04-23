<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Users
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package		Joomla
* @subpackage	Users
*/
class TOOLBAR_users {
	/**
	* Draws the menu to edit a user
	*/
	function _EDIT() {
		$cid = JRequest::getVar( 'cid', array(0) );
		$text = intval($cid[0]) ? JText::_( 'Edit' ) : JText::_( 'Add' );

		JToolBarHelper::title( JText::_( 'User Manager' ) .' - <span>'. $text.'</span>', 'user.png' );
		JToolBarHelper::save();
		JToolBarHelper::apply();
		if ( $cid[0] ) {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		} else {
			JToolBarHelper::cancel();
		}
		JToolBarHelper::help( 'screen.users.edit' );
	}

	function _DEFAULT() {

		JToolBarHelper::title( JText::_( 'User Manager' ), 'user.png' );
		JToolBarHelper::custom( 'logout', 'cancel.png', 'cancel_f2.png', 'Logout' );
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::help( 'screen.users' );
	}
}
?>