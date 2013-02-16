<?php
/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Multilang status helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @since		1.7.1
 */
abstract class multilangstatusHelper
{
	public static function getHomes()
	{
		// Check for multiple Home pages
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from($db->quoteName('#__menu'));
		$query->where('home = 1');
		$query->where('published = 1');
		$query->where('client_id = 0');
		$db->setQuery($query);
		return $db->loadResult();
	}

	/**
	 * @since  1.7.1
	 * @deprecated  3.0
	 */
	public static function getLangfilter()
	{
		JLog::add('multilangstatusHelper::getLangfilter() is deprecated. Use JLanguageMultilang::isEnabled() instead. ', JLog::WARNING, 'deprecated');
		return JLanguageMultilang::isEnabled();
	}

	public static function getLangswitchers()
	{
		// Check if switcher is published
		$db			= JFactory::getDBO();
		$query		= $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from($db->quoteName('#__modules'));
		$query->where('module = ' . $db->quote('mod_languages'));
		$query->where('published = 1');
		$query->where('client_id = 0');
		$db->setQuery($query);
		return $db->loadResult();
	}

	public static function getContentlangs()
	{
		// Check for published Content Languages
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$query->select('a.lang_code AS lang_code');
		$query->select('a.published AS published');
		$query->from('#__languages AS a');
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public static function getSitelangs()
	{
		// check for published Site Languages
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$query->select('a.element AS element');
		$query->from('#__extensions AS a');
		$query->where('a.type = '.$db->Quote('language'));
		$query->where('a.client_id = 0');
		$db->setQuery($query);
		return $db->loadObjectList('element');
	}

	public static function getHomepages()
	{
		// Check for Home pages languages
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$query->select('language');
		$query->from($db->quoteName('#__menu'));
		$query->where('home = 1');
		$query->where('published = 1');
		$query->where('client_id = 0');
		$db->setQuery($query);
		return $db->loadObjectList('language');
	}

	public static function getStatus()
	{
		//check for combined status
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);

		// Select all fields from the languages table.
		$query->select('a.*', 'l.home');
		$query->select('a.published AS published');
		$query->select('a.lang_code AS lang_code');
		$query->from('#__languages AS a');

		// Select the language home pages
		$query->select('l.home AS home');
		$query->select('l.language AS home_language');
		$query->join('LEFT', '#__menu  AS l  ON  l.language = a.lang_code AND l.home=1 AND l.published=1 AND l.language <> \'*\'' );
		$query->select('e.enabled AS enabled');
		$query->select('e.element AS element');
		$query->join('LEFT', '#__extensions  AS e ON e.element = a.lang_code');
		$query->where('e.client_id = 0');
		$query->where('e.enabled = 1');
		$query->where('e.state = 0');

		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public static function getContacts()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('u.name, count(cd.language) as counted, MAX(cd.language='.$db->quote('*').') as all_languages');
		$query->from('#__users AS u');
		$query->leftJOIN('#__contact_details AS cd ON cd.user_id=u.id');
		$query->leftJOIN('#__languages as l on cd.language=l.lang_code');
		$query->where('EXISTS (SELECT * from #__content as c where  c.created_by=u.id)');
		$query->where('(l.published=1 or cd.language='.$db->quote('*').')');
		$query->where('cd.published=1');
		$query->group('u.id');
		$query->having('(counted !=' . count(JLanguageHelper::getLanguages()).' OR all_languages=1)');
		$query->having('(counted !=1 OR all_languages=0)');
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
