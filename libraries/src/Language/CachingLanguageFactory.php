<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Language;

defined('_JEXEC') or die;

/**
 * Caching factory for creating language objects. The requested languages are
 * cached in memory.
 *
 * @since  __DEPLOY_VERSION__
 */
class CachingLanguageFactory extends LanguageFactory
{
	/**
	 * Array of Language objects
	 *
	 * @var    Language[]
	 * @since  __DEPLOY_VERSION__
	 */
	private static $languages = array();

	/**
	 * Method to get an instance of a language.
	 *
	 * @param   string   $lang   The language to use
	 * @param   boolean  $debug  The debug mode
	 *
	 * @return  Language
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function createLanguage($lang, $debug = false): Language
	{
		if (!isset(self::$languages[$lang . $debug]))
		{
			self::$languages[$lang . $debug] = parent::createLanguage($lang, $debug);
		}

		return self::$languages[$lang . $debug];
	}
}
