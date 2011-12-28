<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Finder
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_BASE') or die;

/**
 * System plugin class for Smart Search.
 *
 * @package     Joomla.Plugin
 * @subpackage  System.Finder
 * @since       2.5
 */
class PlgSystemFinder extends JPlugin
{
	/**
	 * Event to trigger the Smart Search update process
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function onAfterDispatch()
	{
		// Pause if the main menu is disabled.
		if (JFactory::getApplication()->input->get('hidemainmenu', '', 'bool'))
		{
			return;
		}

		JHtml::_('behavior.framework');
		JHtml::script('plg_system_finder/status.js', false, true);
	}
}
