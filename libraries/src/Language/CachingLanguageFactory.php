<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Language;

\defined('_JEXEC') or die;

/**
 * Caching factory for creating language objects. The requested languages are
 * cached in memory.
 *
 * @since  4.0.0
 */
class CachingLanguageFactory extends LanguageFactory
{
	/**
	 * Array of Language objects
	 *
	 * @var    Language[]
	 * @since  4.0.0
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
	 * @since   4.0.0
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
