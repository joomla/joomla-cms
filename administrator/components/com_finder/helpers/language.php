<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Finder language helper class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 * @since       2.5
 */
class FinderHelperLanguage
{
	/**
	 * Method to return a plural language code for a taxonomy branch.
	 *
	 * @param   string  Branch title.
	 *
	 * @return  string  Language key code.
	 */
	public static function branchPlural($branchName)
	{
		$return = preg_replace('/[^a-zA-Z0-9]+/', '_', strtoupper($branchName));

		return 'PLG_FINDER_QUERY_FILTER_BRANCH_P_'.$return;
	}

	/**
	 * Method to return a singular language code for a taxonomy branch.
	 *
	 * @param   string  Branch name.
	 *
	 * @return  string  Language key code.
	 */
	public static function branchSingular($branchName)
	{
		$return = preg_replace('/[^a-zA-Z0-9]+/', '_', strtoupper($branchName));

		return 'PLG_FINDER_QUERY_FILTER_BRANCH_S_'.$return;
	}

	/**
	 * Method to determine if the language filter plugin is enabled.
	 * This works for both site and administrator.
	 *
	 * @return  boolean  True if site is supporting multiple languages; false otherwise.
	 *
	 * @since   2.5
	 */
	public static function isMultiLanguage()
	{
		// Flag to avoid doing multiple database queries.
		static $tested = false;

		// Status of language filter plugin.
		static $enabled = false;

		// Get application object.
		$app = JFactory::getApplication();

		// If being called from the front-end, we can avoid the database query.
		if ($app->isSite()) {
			$enabled = $app->getLanguageFilter();
			return $enabled;
		}

		// If already tested, don't test again.
		if (!$tested) {

			// Determine status of language filter plug-in.
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);

			$query->select('enabled');
			$query->from($db->quoteName('#__extensions'));
			$query->where($db->quoteName('type') . ' = ' .  $db->quote('plugin'));
			$query->where($db->quoteName('folder') . ' = ' .  $db->quote('system'));
			$query->where($db->quoteName('element') . ' = ' . $db->quote('languagefilter'));
			$db->setQuery($query);

			$enabled = $db->loadResult();
			$tested = true;
		}

		return $enabled;
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
		$lang = JFactory::getLanguage();
		$lang->load('com_finder', JPATH_SITE);
	}

	/**
	 * Method to load Smart Search plug-in language files.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public static function loadPluginLanguage()
	{
		static $loaded = false;

		// If already loaded, don't load again.
		if ($loaded) {
			return;
		}
		$loaded = true;

		// Get array of all the enabled Smart Search plug-in names.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('name');
		$query->from($db->quoteName('#__extensions'));
		$query->where($db->quoteName('type') . ' = ' .  $db->quote('plugin'));
		$query->where($db->quoteName('folder') . ' = ' .  $db->quote('finder'));
		$query->where($db->quoteName('enabled') . ' = 1');
		$db->setQuery($query);
		$plugins = $db->loadObjectList();

		if (empty($plugins)) {
			return;
		}

		// Load generic language strings.
		$lang = JFactory::getLanguage();
		$lang->load('plg_content_finder', JPATH_ADMINISTRATOR);

		// Load language file for each plug-in.
		foreach ($plugins as $plugin) {
			$lang->load($plugin->name, JPATH_ADMINISTRATOR);
		}
	}

}
