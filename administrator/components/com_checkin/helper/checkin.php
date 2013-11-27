<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Contact component helper.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 * @since       3.2
 */
class CheckinHelperCheckin extends JHelperContent
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	static public function addSubmenu($vName)
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
	}
}