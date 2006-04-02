<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Trash
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
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
* @subpackage Trash
*/
class TOOLBAR_Trash {
	function _DEFAULT() {
		global $task;

		if ( $task == 'viewMenu') {
			$text = ': <small><small>['. JText::_( 'Menu Items' ) .']</small></small>';
		} else {
			$text = ': <small><small>['. JText::_( 'Content Items' ) .']</small></small>';
		}

		JMenuBar::title( JText::_( 'Trash Manager' ) . $text, 'trash.png' );
		JMenuBar::custom('restoreconfirm','restore.png','restore_f2.png', JText::_( 'Restore' ), true);
		JMenuBar::custom('deleteconfirm','delete.png','delete_f2.png', JText::_( 'Delete' ), true);
		JMenuBar::help( 'screen.trashmanager' );
	}

	function _RESTORE() {
		JMenuBar::title( JText::_( 'Restore Items' ), 'restoredb.png' );
		JMenuBar::cancel();
	}

	function _DELETE() {
		JMenuBar::title( JText::_( 'Delete Items' ), 'delete_f2.png' );
		JMenuBar::cancel();
	}
}
?>