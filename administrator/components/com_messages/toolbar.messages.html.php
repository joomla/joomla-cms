<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Messages
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	Messages
 */
class TOOLBAR_messages
{
	function _VIEW() {

		JToolBarHelper::title(JText::_('View Private Message'), 'inbox.png');
		JToolBarHelper::customX('reply', 'restore.png', 'restore_f2.png', 'Reply', false);
		JToolBarHelper::deleteList();
		JToolBarHelper::cancel();
		JToolBarHelper::help('screen.messages.read');
	}

	function _EDIT() {

		JToolBarHelper::title(JText::_('Write Private Message'), 'inbox.png');
		JToolBarHelper::save('save', 'Send');
		JToolBarHelper::cancel();
		JToolBarHelper::help('screen.messages.edit');
	}

	function _CONFIG() {
		JToolBarHelper::title(JText::_('Private Messaging Configuration'), 'inbox.png');
		JToolBarHelper::save('saveconfig');
		JToolBarHelper::cancel('cancelconfig');
		JToolBarHelper::help('screen.messages.conf');
	}

	function _DEFAULT() {
		JToolBarHelper::title(JText::_('Private Messaging'), 'inbox.png');
		JToolBarHelper::deleteList();
		JToolBarHelper::addNewX();
		JToolBarHelper::custom('config', 'config.png', 'config_f2.png', 'Settings', false, false);
		JToolBarHelper::help('screen.messages.inbox');
	}
}