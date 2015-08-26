<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Redirect component helper.
 *
 * @since  1.6
 */
class RedirectHelper
{
	public static $extension = 'com_redirect';

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void.
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($vName)
	{
		// No submenu for this component.
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  JObject
	 *
	 * @deprecated  3.2  Use JHelperContent::getActions() instead
	 */
	public static function getActions()
	{
		// Log usage of deprecated function
		JLog::add(__METHOD__ . '() is deprecated, use JHelperContent::getActions() with new arguments order instead.', JLog::WARNING, 'deprecated');

		// Get list of actions
		$result = JHelperContent::getActions('com_redirect');

		return $result;
	}

	/**
	 * Returns an array of standard published state filter options.
	 *
	 * @return  array  An array containing the options
	 *
	 * @since   1.6
	 */
	public static function publishedOptions()
	{
		// Build the active state filter options.
		$options   = array();
		$options[] = JHtml::_('select.option', '*', 'JALL');
		$options[] = JHtml::_('select.option', '1', 'JENABLED');
		$options[] = JHtml::_('select.option', '0', 'JDISABLED');
		$options[] = JHtml::_('select.option', '2', 'JARCHIVED');
		$options[] = JHtml::_('select.option', '-2', 'JTRASHED');

		return $options;
	}

	/**
	 * Determines if the plugin for Redirect to work is enabled.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public static function isEnabled()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('enabled'))
			->from('#__extensions')
			->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
			->where($db->quoteName('element') . ' = ' . $db->quote('redirect'));
		$db->setQuery($query);

		try
		{
			$result = (boolean) $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		return $result;
	}

	/**
	 * Checks whether the option "Collect URLs" is enabled for the output message
	 *
	 * @return  boolean
	 *
	 * @since   3.4
	 */
	public static function collectUrlsEnabled()
	{
		$collect_urls = false;

		if (JPluginHelper::isEnabled('system', 'redirect'))
		{
			$params       = new Registry(JPluginHelper::getPlugin('system', 'redirect')->params);
			$collect_urls = (bool) $params->get('collect_urls', 1);
		}

		return $collect_urls;
	}
}
