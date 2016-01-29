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
		// Flag to avoid doing multiple database queries.
		static $tested = false;

		// Status of language filter plugin.
		static $enabled = false;

		// Get application object.
		$app = JFactory::getApplication();

		// If being called from the front-end, we can avoid the database query.
		if ($app->isSite())
		{
			$enabled = $app->getLanguageFilter();

			return $enabled;
		}

		// If already tested, don't test again.
		if (!$tested)
		{
			// Determine status of language filter plug-in.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('enabled')
				->from($db->quoteName('#__extensions'))
				->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
				->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
				->where($db->quoteName('element') . ' = ' . $db->quote('languagefilter'));
			$db->setQuery($query);

			$enabled = $db->loadResult();
			$tested = true;
		}

		return $enabled;
	}

	/**
	 * Method to return a list of published site languages.
	 *
	 * @return  array of language extension objects.
	 *
	 * @since   3.5
	 */
	public static function getSiteLangs()
	{
		// To avoid doing duplicate database queries.
		static $multilangSiteLangs = null;

		if (!isset($multilangSiteLangs))
		{
			// Check for published Site Languages.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('element')
				->from('#__extensions')
				->where('type = ' . $db->quote('language'))
				->where('client_id = 0')
				->where('enabled = 1');
			$db->setQuery($query);

			$multilangSiteLangs = $db->loadObjectList('element');
		}

		return $multilangSiteLangs;
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
				->select('level')
				->from($db->quoteName('#__menu'))
				->where('home = 1')
				->where('published = 1')
				->where('client_id = 0');
			$db->setQuery($query);

			$multilangSiteHomePages = $db->loadObjectList('language');
		}

		return $multilangSiteHomePages;
	}

	/**
	 * Get available languages. A available language is published, the language extension is enabled,
	 * has a homepage menu item, the user can view the language and the homepage and the directory of the language exists.	 
	 *
	 * @return  array  An array with all available languages.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getAvailableLanguages()
	{
		static $languages = null;

		if (is_null($languages))
		{
			// Check what languages fullfill the requirements.
			$homepages = JLanguageMultilang::getSiteHomePages();
			$languages = array_intersect_key(JLanguageHelper::getLanguages('lang_code'), JLanguageMultilang::getSiteLangs(), $homepages);
			$levels    = JFactory::getUser()->getAuthorisedViewLevels();

			foreach ($languages as $i => $language)
			{
				// Do not display language without authorized access level in the language.
				if (isset($language->access) && $language->access && !in_array($language->access, $levels))
				{
					unset($languages[$i]);
					continue;
				}

				// Do not display language without authorized access level in the home menu item id.
				if (isset($homepages[$i]->level) && $homepages[$i]->level && !in_array($homepages[$i]->level, $levels))
				{
					unset($languages[$i]);
					continue;
				}

				// Do not display languages without an ini file.
				if (!is_file(JPATH_SITE . '/language/' . $language->lang_code . '/' . $language->lang_code . '.ini'))
				{
					unset($languages['lang_code'][$index]);
					continue;
				}

				// Set the home id for the language.
				$languages[$i]->homeid = $homepages[$i]->id;
			}
		}

		return $languages;
	}
}
