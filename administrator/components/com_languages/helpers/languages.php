<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Languages component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @since		1.6
 */
class LanguagesHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('COM_LANGUAGES_SUBMENU_INSTALLED_SITE'),
			'index.php?option=com_languages&view=installed&client=0',
			$vName == 'installed'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_LANGUAGES_SUBMENU_INSTALLED_ADMINISTRATOR'),
			'index.php?option=com_languages&view=installed&client=1',
			$vName == 'installed'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_LANGUAGES_SUBMENU_CONTENT'),
			'index.php?option=com_languages&view=languages',
			$vName == 'languages'
		);
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 */
	public static function getActions()
	{
		$user		= JFactory::getUser();
		$result		= new JObject;
		$assetName	= 'com_languages';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}
}
