<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Language;

defined('JPATH_PLATFORM') or die;

/**
 * Utitlity class for multilang
 *
 * @since  2.5.4
 */
class Multilanguage
{
	/**
	* Flag indicating multilanguage functionality is enabled.
 	*
 	* @var    boolean
 	* @since  __DEPLOY_VERSION__
 	*/
	public static $enabled = false;

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
		// Flag to avoid doing multiple database queries.
		static $tested = false;

		// Do not proceed with testing if the flag is true
		if (static::$enabled)
		{
			return true;
		}

		// Get application object.
		$app = \JFactory::getApplication();

		// If being called from the frontend, we can avoid the database query.
		if ($app->isClient('site'))
		{
			static::$enabled = $app->getLanguageFilter();

			return static::$enabled;
		}

		// If already tested, don't test again.
		if (!$tested)
		{
			// Determine status of language filter plugin.
			$db = \JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('enabled')
				->from($db->quoteName('#__extensions'))
				->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
				->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
				->where($db->quoteName('element') . ' = ' . $db->quote('languagefilter'));
			$db->setQuery($query);

			static::$enabled = $db->loadResult();
			$tested = true;
		}

		return (bool) static::$enabled;
	}

	/**
	 * Method to return a list of published site languages.
	 *
	 * @return  array of language extension objects.
	 *
	 * @since   3.5
	 * @deprecated   3.7.0  Use \JLanguageHelper::getInstalledLanguages(0) instead.
	 */
	public static function getSiteLangs()
	{
		\JLog::add(__METHOD__ . ' is deprecated. Use \JLanguageHelper::getInstalledLanguages(0) instead.', \JLog::WARNING, 'deprecated');

		return \JLanguageHelper::getInstalledLanguages(0);
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
			$db = \JFactory::getDbo();
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
