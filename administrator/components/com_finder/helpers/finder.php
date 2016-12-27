<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Helper class for Finder.
 *
 * @since  2.5
 */
class FinderHelper
{
	/**
	 * The extension name.
	 *
	 * @var    string
	 * @since  2.5
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
		JHtmlSidebar::addEntry(
			JText::_('COM_FINDER_SUBMENU_INDEX'),
			'index.php?option=com_finder&view=index',
			$vName == 'index'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_FINDER_SUBMENU_MAPS'),
			'index.php?option=com_finder&view=maps',
			$vName == 'maps'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_FINDER_SUBMENU_FILTERS'),
			'index.php?option=com_finder&view=filters',
			$vName == 'filters'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_FINDER_SUBMENU_TERMS'),
			'index.php?option=com_finder&view=terms',
			$vName == 'terms'
		);
	}

	/**
	 * Gets the finder system plugin extension id.
	 *
	 * @return  int  The finder system plugin extension id.
	 *
	 * @since   3.6.0
	 */
	public static function getFinderPluginId()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('content'))
			->where($db->quoteName('element') . ' = ' . $db->quote('finder'));
		$db->setQuery($query);

		try
		{
			$result = (int) $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		return $result;
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  JObject  A JObject containing the allowed actions.
	 *
	 * @since   2.5
	 * @deprecated  3.2  Use JHelperContent::getActions() instead
	 */
	public static function getActions()
	{
		// Log usage of deprecated function
		JLog::add(__METHOD__ . '() is deprecated, use JHelperContent::getActions() with new arguments order instead.', JLog::WARNING, 'deprecated');

		// Get list of actions
		return JHelperContent::getActions('com_finder');
	}
}
