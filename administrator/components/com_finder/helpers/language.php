<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Finder language helper class.
 *
 * @since  2.5
 */
class FinderHelperLanguage
{
	/**
	 * Method to return a plural language code for a taxonomy branch.
	 *
	 * @param   string  $branchName  Branch title.
	 *
	 * @return  string  Language key code.
	 *
	 * @since   2.5
	 */
	public static function branchPlural($branchName)
	{
		$return = preg_replace('/[^a-zA-Z0-9]+/', '_', strtoupper($branchName));

		if ($return !== '_')
		{
			return 'PLG_FINDER_QUERY_FILTER_BRANCH_P_' . $return;
		}

		return $branchName;
	}

	/**
	 * Method to return a singular language code for a taxonomy branch.
	 *
	 * @param   string  $branchName  Branch name.
	 *
	 * @return  string  Language key code.
	 *
	 * @since   2.5
	 */
	public static function branchSingular($branchName)
	{
		$return = preg_replace('/[^a-zA-Z0-9]+/', '_', strtoupper($branchName));

		return 'PLG_FINDER_QUERY_FILTER_BRANCH_S_' . $return;
	}

	/**
	 * Method to return the language name for a language taxonomy branch.
	 *
	 * @param   string  $branchName  Language branch name.
	 *
	 * @return  string  The language title.
	 *
	 * @since   3.6.0
	 */
	public static function branchLanguageTitle($branchName)
	{
		$title = $branchName;

		if ($branchName === '*')
		{
			$title = JText::_('JALL_LANGUAGE');
		}
		else
		{
			$languages = JLanguageHelper::getLanguages('lang_code');

			if (isset($languages[$branchName]))
			{
				$title = $languages[$branchName]->title;
			}
		}

		return $title;
	}

	/**
	 * Method to load Smart Search component language file.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public static function loadComponentLanguage()
	{
		JFactory::getLanguage()->load('com_finder', JPATH_SITE);
	}

	/**
	 * Method to load Smart Search plugin language files.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public static function loadPluginLanguage()
	{
		static $loaded = false;

		// If already loaded, don't load again.
		if ($loaded)
		{
			return;
		}

		$loaded = true;

		// Get array of all the enabled Smart Search plugin names.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select(array($db->qn('name'), $db->qn('element')))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('finder'))
			->where($db->quoteName('enabled') . ' = 1');
		$db->setQuery($query);
		$plugins = $db->loadObjectList();

		if (empty($plugins))
		{
			return;
		}

		// Load generic language strings.
		$lang = JFactory::getLanguage();
		$lang->load('plg_content_finder', JPATH_ADMINISTRATOR);

		// Load language file for each plugin.
		foreach ($plugins as $plugin)
		{
			$lang->load($plugin->name, JPATH_ADMINISTRATOR)
				|| $lang->load($plugin->name, JPATH_PLUGINS . '/finder/' . $plugin->element);
		}
	}
}
