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
