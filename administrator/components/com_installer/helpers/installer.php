<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
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
	public static function addSubmenu($vName = 'install')
	{
		JSubMenuHelper::addEntry(
			JText::_('COM_INSTALLER_SUBMENU_INSTALL'),
			'index.php?option=com_installer',
			$vName == 'install'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_INSTALLER_SUBMENU_UPDATE'),
			'index.php?option=com_installer&view=update',
			$vName == 'update'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_INSTALLER_SUBMENU_MANAGE'),
			'index.php?option=com_installer&view=manage',
			$vName == 'manage'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_INSTALLER_SUBMENU_DISCOVER'),
			'index.php?option=com_installer&view=discover',
			$vName == 'discover'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_INSTALLER_SUBMENU_DATABASE'),
			'index.php?option=com_installer&view=database',
			$vName == 'database'
		);
		JSubMenuHelper::addEntry(
		JText::_('COM_INSTALLER_SUBMENU_WARNINGS'),
					'index.php?option=com_installer&view=warnings',
		$vName == 'warnings'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_INSTALLER_SUBMENU_LANGUAGES'),
			'index.php?option=com_installer&view=languages',
			$vName == 'languages'
		);
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		$assetName = 'com_installer';

		$actions = JAccess::getActions($assetName);

		foreach ($actions as $action) {
			$result->set($action->name,	$user->authorise($action->name, $assetName));
		}

		return $result;
	}
}
