<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Contact
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
* @subpackage	Contact
*/
class TOOLBAR_contact {
	/**
	* Draws the menu for a New Contact
	*/
	function _EDIT() {
		$cid = JRequest::getVar( 'cid', array(0));

		$text = ( $cid[0] ? JText::_( 'Edit' ) : JText::_( 'New' ) );

		JMenuBar::title( JText::_( 'Contact' ) .': <small><small>[ '. $text .' ]</small></small>', 'generic.png' );

		//JMenuBar::custom( 'save2new', 'new.png', 'new_f2.png', 'Save & New', false,  false );
		//JMenuBar::custom( 'save2copy', 'copy.png', 'copy_f2.png', 'Save To Copy', false,  false );
		JMenuBar::save();
		JMenuBar::apply();
		if ( $cid[0] ) {
			// for existing items the button is renamed `close`
			JMenuBar::cancel( 'cancel', 'Close' );
		} else {
			JMenuBar::cancel();
		}
		JMenuBar::help( 'screen.contactmanager.edit' );
	}

	function _DEFAULT() {

		JMenuBar::title( JText::_( 'Contact Manager' ), 'generic.png' );
		JMenuBar::publishList();
		JMenuBar::unpublishList();
		JMenuBar::deleteList();
		JMenuBar::editListX();
		JMenuBar::addNewX();
		JMenuBar::preferences('com_contact', '500');

		JMenuBar::help( 'screen.contactmanager' );
	}
}
?>