<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Languages component helper.
 *
 * @since  1.6
 */
class LanguagesHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName   The name of the active view.
	 * @param   int     $client  The client id of the active view. Maybe be 0 or 1.
	 *
	 * @return  void
	 *
	 * @deprecated  4.0 $client parameter is not needed anymore.
	 */
	public static function addSubmenu($vName, $client = 0)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_LANGUAGES_SUBMENU_INSTALLED'),
			'index.php?option=com_languages&view=installed',
			$vName == 'installed'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_LANGUAGES_SUBMENU_CONTENT'),
			'index.php?option=com_languages&view=languages',
			$vName == 'languages'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_LANGUAGES_SUBMENU_OVERRIDES'),
			'index.php?option=com_languages&view=overrides',
			$vName == 'overrides'
		);
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  JObject
	 *
	 * @deprecated  3.2  Use JHelperContent::getActions() instead.
	 */
	public static function getActions()
	{
		// Log usage of deprecated function.
		try
		{
			JLog::add(
				sprintf('%s() is deprecated. Use JHelperContent::getActions() with new arguments order instead.', __METHOD__),
				JLog::WARNING,
				'deprecated'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		// Get list of actions.
		return JHelperContent::getActions('com_languages');
	}

	/**
	 * Method for parsing ini files.
	 *
	 * @param   string  $filename  Path and name of the ini file to parse.
	 *
	 * @return  array   Array of strings found in the file, the array indices will be the keys. On failure an empty array will be returned.
	 *
	 * @since   2.5
	 */
	public static function parseFile($filename)
	{
		if (!is_file($filename))
		{
			return array();
		}

		$contents = file_get_contents($filename);
		$contents = str_replace('_QQ_', '"\""', $contents);
		$strings  = @parse_ini_string($contents);

		if ($strings === false)
		{
			return array();
		}

		return $strings;
	}

	/**
	 * Filter method for language keys.
	 * This method will be called by JForm while filtering the form data.
	 *
	 * @param   string  $value  The language key to filter.
	 *
	 * @return  string	The filtered language key.
	 *
	 * @since		2.5
	 */
	public static function filterKey($value)
	{
		$filter = JFilterInput::getInstance(null, null, 1, 1);

		return strtoupper($filter->clean($value, 'cmd'));
	}

	/**
	 * Filter method for language strings.
	 * This method will be called by JForm while filtering the form data.
	 *
	 * @param   string  $value  The language string to filter.
	 *
	 * @return  string	The filtered language string.
	 *
	 * @since		2.5
	 */
	public static function filterText($value)
	{
		$filter = JFilterInput::getInstance(null, null, 1, 1);

		return $filter->clean($value);
	}
}
