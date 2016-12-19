<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Language
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utitlity class for multilang
 *
 * @since  2.5.4
 */
class JLanguageMultilang
{
	/**
	 * Method to determine if the language filter plugin is enabled.
	 * This works for both site and administrator.
	 *
	 * @return  boolean  True if site is supporting multiple languages; false otherwise.
	 *
	 * @since   2.5.4
	 */
	public static function isEnabled()
	{
		return JPluginHelper::isEnabled('system', 'languagefilter');
	}

	/**
	 * Method to return a list of published site languages.
	 *
	 * @return  array of language extension objects.
	 *
	 * @since   3.5
	 * @deprecated   __DEPLOY_VERSION__  Use JLanguageHelper::getInstalledLanguages(0) instead.
	 */
	public static function getSiteLangs()
	{
		JLog::add(__METHOD__ . ' is deprecated. Use JLanguageHelper::getInstalledLanguages(0) instead.', JLog::WARNING, 'deprecated');

		return JLanguageHelper::getInstalledLanguages(0);
	}

	/**
	 * Method to return a list of language home page menu items.
	 *
	 * @return  array of menu objects.
	 *
	 * @since   3.5
	 */
	public static function getSiteHomePages()
	{
		// To avoid doing duplicate database queries.
		static $multilangSiteHomePages = null;

		if (!isset($multilangSiteHomePages))
		{
			// Check for Home pages languages.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('language')
				->select('id')
				->from($db->quoteName('#__menu'))
				->where('home = 1')
				->where('published = 1')
				->where('client_id = 0');
			$db->setQuery($query);

			$multilangSiteHomePages = $db->loadObjectList('language');
		}

		return $multilangSiteHomePages;
	}
}
