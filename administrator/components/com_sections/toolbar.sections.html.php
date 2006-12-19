<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Sections
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
* @subpackage Sections
*/
class TOOLBAR_sections {
	/**
	* Draws the menu for Editing an existing category
	*/
	function _EDIT() {
		$cid = JRequest::getVar( 'cid', array(0));

		$text = ( $cid[0] ? JText::_( 'Edit' ) : JText::_( 'New' ) );

		JMenuBar::title( JText::_( 'Section' ).': <small><small>[ '. $text.' ]</small></small>', 'sections.png' );
		JMenuBar::save();
		JMenuBar::apply();
		if ( $cid[0] ) {
			// for existing items the button is renamed `close`
			JMenuBar::cancel( 'cancel', 'Close' );
		} else {
			JMenuBar::cancel();
		}
		JMenuBar::help( 'screen.sections.edit' );
	}
	/**
	* Draws the menu for Copying existing sections
	* @param int The published state (to display the inverse button)
	*/
	function _COPY() {
		JMenuBar::title( JText::_( 'Copy Section' ), 'sections.png' );
		JMenuBar::save( 'copysave' );
		JMenuBar::cancel();
	}
	/**
	* Draws the menu for Editing an existing category
	*/
	function _DEFAULT(){
		JMenuBar::title( JText::_( 'Section Manager' ), 'sections.png' );
		JMenuBar::publishList();
		JMenuBar::unpublishList();
		JMenuBar::customX( 'copyselect', 'copy.png', 'copy_f2.png', 'Copy', true );
		JMenuBar::deleteList();
		JMenuBar::editListX();
		JMenuBar::addNewX();
		JMenuBar::help( 'screen.sections' );
	}
}
?>