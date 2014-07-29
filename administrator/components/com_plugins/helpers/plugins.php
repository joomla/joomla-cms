<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Plugins component helper.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 * @since       1.6
 */
class PluginsHelper
{
	public static $extension = 'com_plugins';

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string    The name of the active view.
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
		$result = JHelperContent::getActions('com_plugins');

		return $result;
	}

	/**
	 * Returns an array of standard published state filter options.
	 *
	 * @return  string    The HTML code for the select tag
	 */
	public static function publishedOptions()
	{
		// Build the active state filter options.
		$options = array();
		$options[] = JHtml::_('select.option', '1', 'JENABLED');
		$options[] = JHtml::_('select.option', '0', 'JDISABLED');

		return $options;
	}

	/**
	 * Returns an array of standard published state filter options.
	 *
	 * @return  string    The HTML code for the select tag
	 */
	public static function folderOptions()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT(folder) AS value, folder AS text')
			->from('#__extensions')
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			->order('folder');

		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		return $options;
	}

	public function parseXMLTemplateFile($templateBaseDir, $templateDir)
	{
		$data = new JObject;

		// Check of the xml file exists
		$filePath = JPath::clean($templateBaseDir . '/templates/' . $templateDir . '/templateDetails.xml');
		if (is_file($filePath))
		{
			$xml = JInstaller::parseXMLInstallFile($filePath);

			if ($xml['type'] != 'template')
			{
				return false;
			}

			foreach ($xml as $key => $value)
			{
				$data->set($key, $value);
			}
		}

		return $data;
	}
}
