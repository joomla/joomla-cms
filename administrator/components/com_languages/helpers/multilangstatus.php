<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\Registry\Registry;

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
		try
		{
			JLog::add(
				sprintf('%s() is deprecated, use JLanguageHelper::getInstalledLanguages(0) instead.', __METHOD__),
				JLog::WARNING,
				'deprecated'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

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
		try
		{
			JLog::add(
				sprintf('%s() is deprecated, use JLanguageHelper::getSiteHomePages() instead.', __METHOD__),
				JLog::WARNING,
				'deprecated'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

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

			if (($warn->alang == 0) && (($warn->slang == 0) && (empty($warn->mlang))))
			{
				unset($warnings[$index]);
			}

			if (($warn->alang == 0) && (($warn->slang == $languages) && (empty($warn->mlang))))
			{
				unset($warnings[$index]);
			}
		}

		return $warnings;
	}

	/**
	 * Method to get the status of the module displaying the menutype of the default Home page set to All languages.
	 *
	 * @return  boolean True if the module is published, false otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getDefaultHomeModule()
	{
		// Find Default Home menutype.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('menutype'))
			->from($db->qn('#__menu'))
			->where($db->qn('home') . ' = ' . $db->q('1'))
			->where($db->qn('published') . ' = ' . $db->q('1'))
			->where($db->qn('client_id') . ' = ' . $db->q('0'))
			->where($db->qn('language') . ' = ' . $db->q('*'));

		$db->setQuery($query);

		$menutype = $db->loadResult();

		// Get published site menu modules titles.
		$query->clear()
			->select($db->qn('title'))
			->from($db->qn('#__modules'))
			->where($db->qn('module') . ' = ' . $db->q('mod_menu'))
			->where($db->qn('published') . ' = ' . $db->q('1'))
			->where($db->qn('client_id') . ' = ' . $db->q('0'));

		$db->setQuery($query);

		$menutitles = $db->loadColumn();

		// Do we have a published menu module displaying the default Home menu item set to all languages?
		foreach ($menutitles as $menutitle)
		{
			$module       = self::getModule('mod_menu', $menutitle);
			$moduleParams = new JRegistry($module->params);
			$param        = $moduleParams->get('menutype', '');

			if ($param && $param != $menutype)
			{
				continue;
			}

			return true;
		}
	}

	/**
	 * Get module by name
	 *
	 * @param   string  $moduleName     The name of the module
	 * @param   string  $instanceTitle  The title of the module, optional
	 *
	 * @return  stdClass  The Module object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getModule($moduleName, $instanceTitle = null)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select('id, title, module, position, content, showtitle, params')
			->from($db->qn('#__modules'))
			->where($db->qn('module') . ' = ' . $db->q($moduleName))
			->where($db->qn('published') . ' = ' . $db->q('1'))
			->where($db->qn('client_id') . ' = ' . $db->q('0'));

		if ($instanceTitle)
		{
			$query->where($db->qn('title') . ' = ' . $db->q($instanceTitle));
		}

		$db->setQuery($query);

		try
		{
			$modules = $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			JLog::add(JText::sprintf('JLIB_APPLICATION_ERROR_MODULE_LOAD', $e->getMessage()), JLog::WARNING, 'jerror');
		}

		return $modules;
	}
}
