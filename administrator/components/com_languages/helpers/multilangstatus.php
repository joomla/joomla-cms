<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Multilang status helper.
 *
 * @since  1.7.1
 */
abstract class MultilangstatusHelper
{
	/**
	 * Method to get the number of published home pages.
	 *
	 * @return  integer
	 */
	public static function getHomes()
	{
		// Check for multiple Home pages.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->quoteName('#__menu'))
			->where('home = 1')
			->where('published = 1')
			->where('client_id = 0');
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Method to get the number of published language switcher modules.
	 *
	 * @return  integer.
	 */
	public static function getLangswitchers()
	{
		// Check if switcher is published.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->quoteName('#__modules'))
			->where('module = ' . $db->quote('mod_languages'))
			->where('published = 1')
			->where('client_id = 0');
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Method to return a list of published content languages.
	 *
	 * @return  array of language objects.
	 */
	public static function getContentlangs()
	{
		// Check for published Content Languages.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.lang_code AS lang_code')
			->select('a.published AS published')
			->from('#__languages AS a');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to return a list of published site languages.
	 *
	 * @return  array of language extension objects.
	 *
	 * @deprecated  4.0  Use JLanguageHelper::getInstalledLanguages(0) instead.
	 */
	public static function getSitelangs()
	{
		JLog::add(__METHOD__ . ' is deprecated, use JLanguageHelper::getInstalledLanguages(0) instead.', JLog::WARNING, 'deprecated');

		return JLanguageHelper::getInstalledLanguages(0);
	}

	/**
	 * Method to return a list of language home page menu items.
	 *
	 * @return  array of menu objects.
	 *
	 * @deprecated  4.0  Use JLanguageMultilang::getSiteHomePages() instead.
	 */
	public static function getHomepages()
	{
		JLog::add(__METHOD__ . ' is deprecated, use JLanguageMultilang::getSiteHomePages() instead.', JLog::WARNING, 'deprecated');

		return JLanguageMultilang::getSiteHomePages();
	}

	/**
	 * Method to return combined language status.
	 *
	 * @return  array of language objects.
	 */
	public static function getStatus()
	{
		// Check for combined status.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Select all fields from the languages table.
		$query->select('a.*', 'l.home')
			->select('a.published AS published')
			->select('a.lang_code AS lang_code')
			->from('#__languages AS a');

		// Select the language home pages.
		$query->select('l.home AS home')
			->select('l.language AS home_language')
			->join('LEFT', '#__menu AS l ON l.language = a.lang_code AND l.home=1 AND l.published=1 AND l.language <> \'*\'')
			->select('e.enabled AS enabled')
			->select('e.element AS element')
			->join('LEFT', '#__extensions  AS e ON e.element = a.lang_code')
			->where('e.client_id = 0')
			->where('e.enabled = 1')
			->where('e.state = 0');

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to return a list of contact objects.
	 *
	 * @return  array of contact objects.
	 */
	public static function getContacts()
	{
		$db = JFactory::getDbo();
		$languages = count(JLanguageHelper::getLanguages());

		// Get the number of contact with all as language
		$alang = $db->getQuery(true)
			->select('count(*)')
			->from('#__contact_details AS cd')
			->where('cd.user_id=u.id')
			->where('cd.published=1')
			->where('cd.language=' . $db->quote('*'));

		// Get the number of languages for the contact
		$slang = $db->getQuery(true)
			->select('count(distinct(l.lang_code))')
			->from('#__languages as l')
			->join('LEFT', '#__contact_details AS cd ON cd.language=l.lang_code')
			->where('cd.user_id=u.id')
			->where('cd.published=1')
			->where('l.published=1');

		// Get the number of multiple contact/language
		$mlang = $db->getQuery(true)
			->select('count(*)')
			->from('#__languages as l')
			->join('LEFT', '#__contact_details AS cd ON cd.language=l.lang_code')
			->where('cd.user_id=u.id')
			->where('cd.published=1')
			->where('l.published=1')
			->group('l.lang_code')
			->having('count(*) > 1');

		// Get the contacts
		$query = $db->getQuery(true)
			->select('u.name, (' . $alang . ') as alang, (' . $slang . ') as slang, (' . $mlang . ') as mlang')
			->from('#__users AS u')
			->join('LEFT', '#__contact_details AS cd ON cd.user_id=u.id')
			->where('EXISTS (SELECT 1 from #__content as c where  c.created_by=u.id)')
			->group('u.id');

		$db->setQuery($query);
		$warnings = $db->loadObjectList();

		foreach ($warnings as $index => $warn)
		{
			if (($warn->alang == 1) && ($warn->slang == 0))
			{
				unset($warnings[$index]);
			}

			if (($warn->alang == 0) && (($warn->slang == 0) && empty($warn->mlang)))
			{
				unset($warnings[$index]);
			}

			if (($warn->alang == 0) && (($warn->slang == $languages) && empty($warn->mlang)))
			{
				unset($warnings[$index]);
			}
		}

		return $warnings;
	}
}
