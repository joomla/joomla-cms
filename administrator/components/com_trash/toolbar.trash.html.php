<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @subpackage	Trash
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package		Joomla.Administrator
* @subpackage	Trash
*/
class TOOLBAR_Trash {
	function _DEFAULT() {
		$task	= JRequest::getCmd('task', 'viewMenu');
		$return	= JRequest::getCmd('return', 'viewContent', 'post');

		if ( $task == 'viewMenu' || $return == 'viewMenu') {
			$text = ': <small><small>['. JText::_( 'Menu Items' ) .']</small></small>';
		} else {
			$text = ': <small><small>['. JText::_( 'Articles' ) .']</small></small>';
		}

		JToolBarHelper::title( JText::_( 'Trash Manager' ) . $text, 'trash.png' );
		JToolBarHelper::custom('restoreconfirm','restore.png','restore_f2.png', 'Restore', true);
		JToolBarHelper::custom('deleteconfirm','delete.png','delete_f2.png', 'Delete', true);
		JToolBarHelper::help( 'screen.trashmanager' );
	}

	function _RESTORE() {
		JToolBarHelper::title( JText::_( 'Restore Items' ), 'restoredb.png' );
		JToolBarHelper::cancel();
	}

	function _DELETE() {
		JToolBarHelper::title( JText::_( 'Delete Items' ), 'delete_f2.png' );
		JToolBarHelper::cancel();
	}
}