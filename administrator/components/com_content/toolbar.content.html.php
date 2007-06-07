<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Content
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* @package		Joomla
* @subpackage	Content
*/
class TOOLBAR_content
{
	function _EDIT()
	{
		$cid = JRequest::getVar( 'cid', array(0), '', 'array' );
		$cid = intval($cid[0]);

		$text = ( $cid ? JText::_( 'Edit' ) : JText::_( 'New' ) );

		JToolBarHelper::title( JText::_( 'Article' ).': <small><small>[ '. $text.' ]</small></small>', 'addedit.png' );
		JToolBarHelper::preview( 'index.php?option=com_content&id='.$cid.'&tmpl=component', true );
		JToolBarHelper::save();
		JToolBarHelper::apply();
		if ( $cid ) {
			// for existing articles the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		} else {
			JToolBarHelper::cancel();
		}
		JToolBarHelper::help( 'screen.content.edit' );
	}
/*
	function _ARCHIVE() 
	{
		JToolBarHelper::title( JText::_( 'Archive Manager' ), 'addedit.png' );
		JToolBarHelper::unarchiveList();
		JToolBarHelper::custom( 'remove', 'delete.png', 'delete_f2.png', 'Trash', false );
		JToolBarHelper::help( 'screen.content.archive' );
	}
*/
	function _MOVE() 
	{
		JToolBarHelper::title( JText::_( 'Move Articles' ), 'move_f2.png' );
		JToolBarHelper::custom( 'movesectsave', 'save.png', 'save_f2.png', 'Save', false );
		JToolBarHelper::cancel();
	}

	function _COPY() 
	{
		JToolBarHelper::title( JText::_( 'Copy Articles' ), 'copy_f2.png' );
		JToolBarHelper::custom( 'copysave', 'save.png', 'save_f2.png', 'Save', false );
		JToolBarHelper::cancel();
	}

	function _DEFAULT() 
	{
		global $filter_state;

		JToolBarHelper::title( JText::_( 'Article Manager' ), 'addedit.png' );
		if ($filter_state == 'A' || $filter_state == NULL) {
			JToolBarHelper::unarchiveList();
		}
		if ($filter_state != 'A') {
			JToolBarHelper::archiveList();
		}
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::customX( 'movesect', 'move.png', 'move_f2.png', 'Move' );
		JToolBarHelper::customX( 'copy', 'copy.png', 'copy_f2.png', 'Copy' );
		JToolBarHelper::trash();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::preferences('com_content', '550');
		JToolBarHelper::help( 'screen.content' );
	}
}
?>