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
class TOOLBAR_typedcontent {
	function _EDIT( ) {
		global $id;
		
		$text = ( $id ? JText::_( 'Edit' ) : JText::_( 'New' ) );
		
		JMenuBar::title( JText::_( 'Static Content Item' ).': <small>'. $text .'</small>', 'addedit.png' );
		JMenuBar::preview( 'contentwindow', true );
		JMenuBar::media_manager();
		JMenuBar::trash('remove', 'Trash', false);
		JMenuBar::save();
		JMenuBar::apply();
		JMenuBar::cancel();
		JMenuBar::help( 'screen.staticcontent.edit' );
	}

	function _MOVE() {		
		JMenuBar::title( JText::_( 'Move Static Content' ), 'move_f2.png' );
		JMenuBar::custom( 'movesave', 'save.png', 'save_f2.png', JText::_( 'Save' ), false );
		JMenuBar::cancel();
	}
	
	function _DEFAULT() {
		JMenuBar::title( JText::_( 'Static Content Manager' ), 'addedit.png' );
		JMenuBar::publishList();
		JMenuBar::unpublishList();
		JMenuBar::customX( 'move', 'move.png', 'move_f2.png', JText::_( 'Move' ) );
		JMenuBar::customX( 'copy', 'copy.png', 'copy_f2.png', JText::_( 'Copy' ) );
		JMenuBar::trash();
		JMenuBar::editListX( 'editA' );
		JMenuBar::addNewX();
		JMenuBar::help( 'screen.staticcontent' );
	}
}
?>