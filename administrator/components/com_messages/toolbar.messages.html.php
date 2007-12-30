<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Messages
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package		Joomla
* @subpackage	Messages
*/
class TOOLBAR_messages
{
	function _VIEW() {

		JToolBarHelper::title(  JText::_( 'View Private Message' ), 'inbox.png' );
		JToolBarHelper::customX('reply', 'restore.png', 'restore_f2.png', 'Reply', false );
		JToolBarHelper::deleteList();
		JToolBarHelper::cancel();
	}

	function _EDIT() {

		JToolBarHelper::title(  JText::_( 'Write Private Message' ), 'inbox.png' );
		JToolBarHelper::save( 'save', 'Send' );
		JToolBarHelper::cancel();
		JToolBarHelper::help( 'screen.messages.edit' );
	}

	function _CONFIG() {
		JToolBarHelper::title(  JText::_( 'Private Messaging Configuration' ), 'inbox.png' );
		JToolBarHelper::save( 'saveconfig' );
		JToolBarHelper::cancel( 'cancelconfig' );
		JToolBarHelper::help( 'screen.messages.conf' );
	}

	function _DEFAULT() {
		JToolBarHelper::title(  JText::_( 'Private Messaging' ), 'inbox.png' );
		JToolBarHelper::deleteList();
		JToolBarHelper::addNewX();
		JToolBarHelper::custom('config', 'config.png', 'config_f2.png', 'Settings', false, false);
		JToolBarHelper::help( 'screen.messages.inbox' );
	}
}