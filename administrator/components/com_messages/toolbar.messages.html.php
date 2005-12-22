<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Messages
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
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
* @subpackage Messages
*/
class TOOLBAR_messages {
	function _VIEW() {

		JMenuBar::startTable();
		JMenuBar::title(  JText::_( 'View Private Message' ), 'inbox.png' );
		JMenuBar::customX('reply', 'restore.png', 'restore_f2.png', JText::_( 'Reply' ), false );
		JMenuBar::spacer();
		JMenuBar::deleteList();
		JMenuBar::spacer();
		JMenuBar::cancel();
		JMenuBar::endTable();
	}

	function _EDIT() {

		JMenuBar::startTable();
		JMenuBar::title(  JText::_( 'New Private Message' ), 'inbox.png' );
		JMenuBar::save( 'save', JText::_( 'Send' ) );
		JMenuBar::spacer();
		JMenuBar::cancel();
		JMenuBar::spacer();
		JMenuBar::help( 'screen.messages.edit' );
		JMenuBar::endTable();
	}

	function _CONFIG() {
		JMenuBar::startTable();
		JMenuBar::title(  JText::_( 'Private Messaging Configuration' ), 'inbox.png' );
		JMenuBar::save( 'saveconfig' );
		JMenuBar::spacer();
		JMenuBar::cancel( 'cancelconfig' );
		JMenuBar::spacer();
		JMenuBar::help( 'screen.messages.conf' );
		JMenuBar::endTable();
	}

	function _DEFAULT() {
		JMenuBar::startTable();
		JMenuBar::title(  JText::_( 'Private Messaging' ), 'inbox.png' );
		JMenuBar::deleteList();
		JMenuBar::spacer();
		JMenuBar::addNewX();
		JMenuBar::spacer();
		JMenuBar::help( 'screen.messages.inbox' );
		JMenuBar::endTable();
	}
}
?>