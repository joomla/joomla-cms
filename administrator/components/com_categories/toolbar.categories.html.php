<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Categories
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* @package Joomla
* @subpackage Categories
*/
class TOOLBAR_categories {
	/**
	* Draws the menu for Editing an existing category
	* @param int The published state (to display the inverse button)
	*/
	function _EDIT() {
		global $id;

		$text = ( $id ? JText::_( 'Edit' ) : JText::_( 'New' ) );

		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Category' ) .': '. $text, 'categories.png' );
		mosMenuBar::media_manager();
		mosMenuBar::spacer();
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
		mosMenuBar::help( 'screen.categories.edit' );
		mosMenuBar::endTable();
	}
	/**
	* Draws the menu for Moving existing categories
	* @param int The published state (to display the inverse button)
	*/
	function _MOVE() {

		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Move Category' ) );
		mosMenuBar::save( 'movesave' );
		mosMenuBar::spacer();
		mosMenuBar::cancel();
		mosMenuBar::endTable();
	}
	/**
	* Draws the menu for Copying existing categories
	* @param int The published state (to display the inverse button)
	*/
	function _COPY() {

		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Copy Category' ) );
		mosMenuBar::save( 'copysave' );
		mosMenuBar::spacer();
		mosMenuBar::cancel();
		mosMenuBar::endTable();
	}
	/**
	* Draws the menu for Editing an existing category
	*/
	function _DEFAULT(){

		$section = mosGetParam( $_REQUEST, 'section', '' );

		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Category Manager' ), 'categories.png' );
		mosMenuBar::publishList();
		mosMenuBar::spacer();
		mosMenuBar::unpublishList();
		mosMenuBar::spacer();
		if ( $section == 'content' || ( $section > 0 ) ) {
			mosMenuBar::customX( 'moveselect', 'move.png', 'move_f2.png', JText::_( 'Move' ), true );
			mosMenuBar::spacer();
			mosMenuBar::customX( 'copyselect', 'copy.png', 'copy_f2.png', JText::_( 'Copy' ), true );
			mosMenuBar::spacer();
		}
		mosMenuBar::deleteList();
		mosMenuBar::spacer();
		mosMenuBar::editListX();
		mosMenuBar::spacer();
		mosMenuBar::addNewX();
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.categories' );
		mosMenuBar::endTable();
	}
}
?>