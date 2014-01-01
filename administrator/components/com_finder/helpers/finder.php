<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Helper class for Finder.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 * @since       2.5
 */
class FinderHelper
{
	/**
	 * @var		string	The extension name.
	 * @since	2.5
	 */
	public static $extension = 'com_finder';

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('COM_FINDER_SUBMENU_INDEX'),
			'index.php?option=com_finder&view=index',
			$vName == 'index'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_FINDER_SUBMENU_MAPS'),
			'index.php?option=com_finder&view=maps',
			$vName == 'maps'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_FINDER_SUBMENU_FILTERS'),
			'index.php?option=com_finder&view=filters',
			$vName == 'filters'
		);
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  JObject  A JObject containing the allowed actions.
	 *
	 * @since   2.5
	 */
	public static function getActions()
	{
		$user = JFactory::getUser();
		$result = new JObject;
		$assetName = 'com_finder';

		$actions = JAccess::getActions($assetName, 'component');

		foreach ($actions as $action)
		{
			$result->set($action->name, $user->authorise($action->name, $assetName));
		}

		return $result;
	}
}
