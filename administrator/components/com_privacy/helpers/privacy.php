<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Privacy component helper.
 *
 * @since  __DEPLOY_VERSION__
 */
class PrivacyHelper extends JHelperContent
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_PRIVACY_SUBMENU_REQUESTS'),
			'index.php?option=com_privacy',
			$vName === 'requests'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_PRIVACY_SUBMENU_CAPABILITIES'),
			'index.php?option=com_privacy&view=capabilities',
			$vName === 'capabilities'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_PRIVACY_SUBMENU_CONSENTS'),
			'index.php?option=com_privacy&view=consents',
			$vName === 'consents'
		);
	}
}
