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
	function _EDIT($edit) {
		$cid = JRequest::getVar( 'cid', array(0), '', 'array' );

		$text = ( $edit ? JText::_( 'Edit' ) : JText::_( 'New' ) );

		JToolBarHelper::title( JText::_( 'Contact' ) .': <small><small>[ '. $text .' ]</small></small>', 'generic.png' );

		//JToolBarHelper::custom( 'save2new', 'new.png', 'new_f2.png', 'Save & New', false,  false );
		//JToolBarHelper::custom( 'save2copy', 'copy.png', 'copy_f2.png', 'Save To Copy', false,  false );
		JToolBarHelper::save();
		JToolBarHelper::apply();
		if ( $edit ) {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		} else {
			JToolBarHelper::cancel();
		}
		JToolBarHelper::help( 'screen.contactmanager.edit' );
	}

	function _DEFAULT() {

		JToolBarHelper::title( JText::_( 'Contact Manager' ), 'generic.png' );
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::preferences('com_contact', '650');

		JToolBarHelper::help( 'screen.contactmanager' );
	}
}
?>