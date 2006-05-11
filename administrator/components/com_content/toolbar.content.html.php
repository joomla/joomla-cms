<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Content
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
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
class TOOLBAR_content
{
	function _EDIT() {

		$cid = JRequest::getVar( 'cid', array(0), '', 'array' );
		$cid = intval($cid[0]);

		$text = ( $cid ? JText::_( 'Edit' ) : JText::_( 'New' ) );

		JMenuBar::title( JText::_( 'Article' ).': <small><small>[ '. $text.' ]</small></small>', 'addedit.png' );
		JMenuBar::preview( 'index.php?option=com_content&id='.$cid.'&tmpl=component.html', true );
		JMenuBar::media_manager();
		JMenuBar::trash('remove', 'Trash', false);
		JMenuBar::save();
		JMenuBar::apply();
		if ( $cid ) {
			// for existing content items the button is renamed `close`
			JMenuBar::cancel( 'cancel', JText::_( 'Close' ) );
		} else {
			JMenuBar::cancel();
		}
		JMenuBar::help( 'screen.content.edit' );
	}

	function _ARCHIVE() {

		JMenuBar::title( JText::_( 'Archive Manager' ), 'addedit.png' );
		JMenuBar::unarchiveList();
		JMenuBar::custom( 'remove', 'delete.png', 'delete_f2.png', 'Trash', false );
		JMenuBar::help( 'screen.content.archive' );
	}

	function _MOVE() {

		JMenuBar::title( JText::_( 'Move Content Items' ), 'move_f2.png' );
		JMenuBar::custom( 'movesectsave', 'save.png', 'save_f2.png', 'Save', false );
		JMenuBar::cancel();
	}

	function _COPY() {

		JMenuBar::title( JText::_( 'Copy Content Items' ), 'copy_f2.png' );
		JMenuBar::custom( 'copysave', 'save.png', 'save_f2.png', 'Save', false );
		JMenuBar::cancel();
	}

	function _DEFAULT() {

		JMenuBar::title( JText::_( 'Article Manager' ), 'addedit.png' );
		JMenuBar::archiveList();
		JMenuBar::publishList();
		JMenuBar::unpublishList();
		JMenuBar::customX( 'movesect', 'move.png', 'move_f2.png', 'Move' );
		JMenuBar::customX( 'copy', 'copy.png', 'copy_f2.png', 'Copy' );
		JMenuBar::trash();
		JMenuBar::editListX();
		JMenuBar::addNewX();
		JMenuBar::help( 'screen.content' );
	}
}
?>