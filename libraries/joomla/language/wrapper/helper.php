<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Language
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Wrapper class for JLanguageHelper
 *
 * @package     Joomla.Platform
 * @subpackage  Language
 * @since       3.4
 */
class JLanguageWrapperHelper
{
	/**
	 * Helper wrapper method for createLanguageList
	 *
	 * @param   string   $actualLanguage  Client key for the area.
	 * @param   string   $basePath        Base path to use.
	 * @param   boolean  $caching         True if caching is used.
	 * @param   boolean  $installed       Get only installed languages.
	 *
	 * @return  array  List of system languages.
	 *
	 * @see     JLanguageHelper::createLanguageList
	 * @since   3.4
	 */
	public function createLanguageList($actualLanguage, $basePath = JPATH_BASE, $caching = false, $installed = false)
	{
		return JLanguageHelper::createLanguageList($actualLanguage, $basePath, $caching, $installed);
	}

	/**
	 * Helper wrapper method for detectLanguage
	 *
	 * @return  string  locale or null if not found.
	 *
	 * @see     JLanguageHelper::detectLanguage
	 * @since   3.4
	 */
	public function detectLanguage()
	{
		return JLanguageHelper::detectLanguage();
	}

	/**
	 * Helper wrapper method for getLanguages
	 *
	 * @param   string  $key  Array key
	 *
	 * @return  array  An array of published languages.
	 *
	 * @see     JLanguageHelper::getLanguages
	 * @since   3.4
	 */
	public function getLanguages($key = 'default')
	{
		return JLanguageHelper::getLanguages($key);
	}
}
