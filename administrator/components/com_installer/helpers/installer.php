<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Installer helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @since		1.6
 */
class InstallerHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('Installer_Submenu_Install'),
			'index.php?option=com_installer',
			$vName == 'install'
		);
		JSubMenuHelper::addEntry(
			JText::_('Installer_Submenu_Update'),
			'index.php?option=com_installer&view=update',
			$vName == 'update'
		);
		JSubMenuHelper::addEntry(
			JText::_('Installer_Submenu_Manage'),
			'index.php?option=com_installer&view=manage',
			$vName == 'manage'
		);
		JSubMenuHelper::addEntry(
			JText::_('Installer_Submenu_Discover'),
			'index.php?option=com_installer&view=discover',
			$vName == 'discover'
		);
		JSubMenuHelper::addEntry(
			JText::_('Installer_Submenu_Warnings'),
			'index.php?option=com_installer&view=warnings',
			$vName == 'warnings'
		);
	}
}