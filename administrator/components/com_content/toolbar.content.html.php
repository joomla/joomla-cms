<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Content
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
* @subpackage Content
*/
class TOOLBAR_content {
	function _EDIT() {
		global $id;

		mosMenuBar::startTable();
		mosMenuBar::preview( 'contentwindow', true );
		mosMenuBar::spacer();
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
		mosMenuBar::help( 'screen.content.edit' );
		mosMenuBar::endTable();
	}

	function _ARCHIVE() {

		mosMenuBar::startTable();
		mosMenuBar::unarchiveList();
		mosMenuBar::spacer();
		mosMenuBar::custom( 'remove', 'delete.png', 'delete_f2.png', JText::_( 'Trash' ), false );
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.content.archive' );
		mosMenuBar::endTable();
	}

	function _MOVE() {

		mosMenuBar::startTable();
		mosMenuBar::custom( 'movesectsave', 'save.png', 'save_f2.png', JText::_( 'Save' ), false );
		mosMenuBar::spacer();
		mosMenuBar::cancel();
		mosMenuBar::endTable();
	}

	function _COPY() {

		mosMenuBar::startTable();
		mosMenuBar::custom( 'copysave', 'save.png', 'save_f2.png', JText::_( 'Save' ), false );
		mosMenuBar::spacer();
		mosMenuBar::cancel();
		mosMenuBar::endTable();
	}

	function _DEFAULT() {

		mosMenuBar::startTable();
		mosMenuBar::archiveList();
		mosMenuBar::spacer();
		mosMenuBar::publishList();
		mosMenuBar::spacer();
		mosMenuBar::unpublishList();
		mosMenuBar::spacer();
		mosMenuBar::customX( 'movesect', 'move.png', 'move_f2.png', JText::_( 'Move' ) );
		mosMenuBar::spacer();
		mosMenuBar::customX( 'copy', 'copy.png', 'copy_f2.png', JText::_( 'Copy' ) );
		mosMenuBar::spacer();
		mosMenuBar::trash();
		mosMenuBar::spacer();
		mosMenuBar::editListX( 'editA' );
		mosMenuBar::spacer();
		mosMenuBar::addNewX();
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.content' );
		mosMenuBar::endTable();
	}
}
?>