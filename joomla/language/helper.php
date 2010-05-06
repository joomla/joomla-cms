<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Language
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * @package		Joomla.Framework
 * @subpackage	Language
 * @static
 * @since 1.5
 */
class JLanguageHelper
{
	/**
	 * Builds a list of the system languages which can be used in a select option
	 *
	 * @param	string	Client key for the area
	 * @param	string	Base path to use
	 * @param	array	An array of arrays (text, value, selected)
	 * @since	1.5
	 */
	public static function createLanguageList($actualLanguage, $basePath = JPATH_BASE, $caching = false)
	{
		$list = array ();

		// cache activation
		$langs = JLanguage::getKnownLanguages($basePath);

		foreach ($langs as $lang => $metadata)
		{
			$option = array ();

			$option['text'] = $metadata['name'];
			$option['value'] = $lang;
			if ($lang == $actualLanguage) {
				$option['selected'] = 'selected="selected"';
			}
			$list[] = $option;
		}

		return $list;
	}

	/**
	 * Tries to detect the language.
	 *
	 * @return	string locale or null if not found
	 * @since	1.5
	 */
	public static function detectLanguage()
	{
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		{
			$systemLangs	= self::getLanguages();
			$browserLangs	= explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			
			foreach ($browserLangs as $browserLang)
			{
				// slice out the part before ; on first step, the part before - on second, place into array
				$browserLang = substr($browserLang, 0, strcspn($browserLang, ';'));
				$primary_browserLang = substr($browserLang, 0, 2);

				foreach($systemLangs as $systemLang)
				{
					// take off 3 letters iso code languages as they can't match browsers' languages and default them to en
					$Jinstall_lang = $systemLang->lang_code;

					if (strlen($Jinstall_lang) < 6)
					{
						if (strtolower($browserLang) == strtolower(substr($systemLang->lang_code, 0, strlen($browserLang)))) {
							return $systemLang->lang_code;
						}
						else if ($primary_browserLang == substr($systemLang->lang_code, 0, 2)) {
							$primaryDetectedLang = $systemLang->lang_code;
						}
					}
				}

				if (isset($primaryDetectedLang)) {
					return $primaryDetectedLang;
				}
			}
		}

		return null;
	}

	/**
	 * Get available languages
	 *
	 * @param	string array key
	 * @return	array of published languages
	 * @since	1.6
	 */
	public static function getLanguages($key='default')
	{
		static $languages;
		
		if (empty($languages)) {
			$cache = JFactory::getCache('com_languages', '');
			if (!$languages = $cache->get('languages')) {
				$db 	= JFactory::getDBO();
				$query	= $db->getQuery(true);
				$query->select('*')->from('#__languages')->where('published=1');
				$db->setQuery($query);
				
				$languages['default'] 	= $db->loadObjectList();
				$languages['sef']		= array();
				$languages['lang_code']	= array();
				
				if (isset($languages['default'][0])) {
					foreach($languages['default'] as $lang) {
						$languages['sef'][$lang->sef] 				= $lang; 
						$languages['lang_code'][$lang->lang_code] 	= $lang; 
					}
				}
				
				$cache->store($languages, 'languages');
			}
		}
		return $languages[$key];
	}
}
