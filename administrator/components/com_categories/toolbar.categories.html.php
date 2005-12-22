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

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Category' ) .': '. $text, 'categories.png' );
		JMenuBar::media_manager();
		JMenuBar::spacer();
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
		JMenuBar::help( 'screen.categories.edit' );
		JMenuBar::endTable();
	}
	/**
	* Draws the menu for Moving existing categories
	* @param int The published state (to display the inverse button)
	*/
	function _MOVE() {

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Move Category' ) );
		JMenuBar::save( 'movesave' );
		JMenuBar::spacer();
		JMenuBar::cancel();
		JMenuBar::endTable();
	}
	/**
	* Draws the menu for Copying existing categories
	* @param int The published state (to display the inverse button)
	*/
	function _COPY() {

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Copy Category' ) );
		JMenuBar::save( 'copysave' );
		JMenuBar::spacer();
		JMenuBar::cancel();
		JMenuBar::endTable();
	}
	/**
	* Draws the menu for Editing an existing category
	*/
	function _DEFAULT(){

		$section = mosGetParam( $_REQUEST, 'section', '' );

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Category Manager' ), 'categories.png' );
		JMenuBar::publishList();
		JMenuBar::spacer();
		JMenuBar::unpublishList();
		JMenuBar::spacer();
		if ( $section == 'content' || ( $section > 0 ) ) {
			JMenuBar::customX( 'moveselect', 'move.png', 'move_f2.png', JText::_( 'Move' ), true );
			JMenuBar::spacer();
			JMenuBar::customX( 'copyselect', 'copy.png', 'copy_f2.png', JText::_( 'Copy' ), true );
			JMenuBar::spacer();
		}
		JMenuBar::deleteList();
		JMenuBar::spacer();
		JMenuBar::editListX();
		JMenuBar::spacer();
		JMenuBar::addNewX();
		JMenuBar::spacer();
		JMenuBar::help( 'screen.categories' );
		JMenuBar::endTable();
	}
}
?>