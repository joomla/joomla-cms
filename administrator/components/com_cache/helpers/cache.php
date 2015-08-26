<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Cache component helper.
 *
 * @since  1.6
 */
class CacheHelper
{
	/**
	 * Get a list of filter options for the application clients.
	 *
	 * @return  array  An array of JHtmlOption elements.
	 */
	public static function getClientOptions()
	{
		// Build the filter options.
		$options   = array();
		$options[] = JHtml::_('select.option', '0', JText::_('JSITE'));
		$options[] = JHtml::_('select.option', '1', JText::_('JADMINISTRATOR'));

		return $options;
	}

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('JGLOBAL_SUBMENU_CHECKIN'),
			'index.php?option=com_checkin',
			$vName == 'com_checkin'
		);

		JHtmlSidebar::addEntry(
			JText::_('JGLOBAL_SUBMENU_CLEAR_CACHE'),
			'index.php?option=com_cache',
			$vName == 'cache'
		);
		JHtmlSidebar::addEntry(
			JText::_('JGLOBAL_SUBMENU_PURGE_EXPIRED_CACHE'),
			'index.php?option=com_cache&view=purge',
			$vName == 'purge'
		);
	}
}
