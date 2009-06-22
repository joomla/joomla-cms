<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Users component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_weblinks
 * @since		1.6
 */
class UsersHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('Users_Submenu_Users'),
			'index.php?option=com_users&view=users',
			$vName == 'users'
		);
		JSubMenuHelper::addEntry(
			JText::_('Users_Submenu_Groups'),
			'index.php?option=com_users&view=groups',
			$vName == 'groups'
		);
		JSubMenuHelper::addEntry(
			JText::_('Users_Submenu_Levels'),
			'index.php?option=com_users&view=levels',
			$vName == 'levels'
		);
	}
}