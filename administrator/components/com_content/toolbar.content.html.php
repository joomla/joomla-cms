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
		
		$text = ( $id ? JText::_( 'Edit' ) : JText::_( 'New' ) );
		
		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Content Item' ).': <small><small>'. $text .'</small></small>', 'addedit.png' );
		JMenuBar::preview( 'index3.php?option=com_content&id='.$id, true );
		JMenuBar::spacer();
		JMenuBar::media_manager();
		JMenuBar::spacer();
		JMenuBar::trash('remove', 'Trash', false);
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
		JMenuBar::help( 'screen.content.edit' );
		JMenuBar::endTable();
	}

	function _ARCHIVE() {

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Archive Manager' ), 'addedit.png' );
		JMenuBar::unarchiveList();
		JMenuBar::spacer();
		JMenuBar::custom( 'remove', 'delete.png', 'delete_f2.png', JText::_( 'Trash' ), false );
		JMenuBar::spacer();
		JMenuBar::help( 'screen.content.archive' );
		JMenuBar::endTable();
	}

	function _MOVE() {

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Move Content Items' ), 'move_f2.png' );
		JMenuBar::custom( 'movesectsave', 'save.png', 'save_f2.png', JText::_( 'Save' ), false );
		JMenuBar::spacer();
		JMenuBar::cancel();
		JMenuBar::endTable();
	}

	function _COPY() {

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Copy Content Items' ), 'copy_f2.png' );
		JMenuBar::custom( 'copysave', 'save.png', 'save_f2.png', JText::_( 'Save' ), false );
		JMenuBar::spacer();
		JMenuBar::cancel();
		JMenuBar::endTable();
	}

	function _DEFAULT() {

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Content Items Manager' ), 'addedit.png' );
		JMenuBar::archiveList();
		JMenuBar::spacer();
		JMenuBar::publishList();
		JMenuBar::spacer();
		JMenuBar::unpublishList();
		JMenuBar::spacer();
		JMenuBar::customX( 'movesect', 'move.png', 'move_f2.png', JText::_( 'Move' ) );
		JMenuBar::spacer();
		JMenuBar::customX( 'copy', 'copy.png', 'copy_f2.png', JText::_( 'Copy' ) );
		JMenuBar::spacer();
		JMenuBar::trash();
		JMenuBar::spacer();
		JMenuBar::editListX( 'editA' );
		JMenuBar::spacer();
		JMenuBar::addNewX();
		JMenuBar::spacer();
		JMenuBar::help( 'screen.content' );
		JMenuBar::endTable();
	}
}
?>