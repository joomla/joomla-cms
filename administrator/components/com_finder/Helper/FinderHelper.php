<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

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
		\JHtmlSidebar::addEntry(
			Text::_('COM_FINDER_SUBMENU_INDEX'),
			'index.php?option=com_finder&view=index',
			$vName === 'index'
		);
		\JHtmlSidebar::addEntry(
			Text::_('COM_FINDER_SUBMENU_MAPS'),
			'index.php?option=com_finder&view=maps',
			$vName === 'maps'
		);
		\JHtmlSidebar::addEntry(
			Text::_('COM_FINDER_SUBMENU_FILTERS'),
			'index.php?option=com_finder&view=filters',
			$vName === 'filters'
		);
		\JHtmlSidebar::addEntry(
			Text::_('COM_FINDER_SUBMENU_SEARCHES'),
			'index.php?option=com_finder&view=searches',
			$vName === 'searches'
		);
	}

	/**
	 * Gets the finder system plugin extension id.
	 *
	 * @return  integer  The finder system plugin extension id.
	 *
	 * @since   3.6.0
	 */
	public static function getFinderPluginId()
	{
		$db    = Factory::getDbo();
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
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		return $result;
	}
}
