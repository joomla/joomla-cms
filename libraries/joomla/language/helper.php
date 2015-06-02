<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Language
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Language helper class
 *
 * @since  11.1
 */
class JLanguageHelper
{
	/**
	 * Builds a list of the system languages which can be used in a select option
	 *
	 * @param   string   $actualLanguage  Client key for the area
	 * @param   string   $basePath        Base path to use
	 * @param   boolean  $caching         True if caching is used
	 * @param   boolean  $installed       Get only installed languages
	 *
	 * @return  array  List of system languages
	 *
	 * @since   11.1
	 */
	public static function createLanguageList($actualLanguage, $basePath = JPATH_BASE, $caching = false, $installed = false)
	{
		$list = array();

		// Cache activation
		$langs = JLanguage::getKnownLanguages($basePath);

		if ($installed)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('element')
				->from('#__extensions')
				->where('type=' . $db->quote('language'))
				->where('state=0')
				->where('enabled=1')
				->where('client_id=' . ($basePath == JPATH_ADMINISTRATOR ? 1 : 0));
			$db->setQuery($query);
			$installed_languages = $db->loadObjectList('element');
		}

		foreach ($langs as $lang => $metadata)
		{
			if (!$installed || array_key_exists($lang, $installed_languages))
			{
				$option = array();

				$option['text'] = $metadata['name'];
				$option['value'] = $lang;

				if ($lang == $actualLanguage)
				{
					$option['selected'] = 'selected="selected"';
				}

				$list[] = $option;
			}
		}

		return $list;
	}

	/**
	 * Tries to detect the browser language.
	 *
	 * @return  mixed  string  Best match for locale amongst those available
	 *                 null    No match
	 *
	 * Standard for HTTP_ACCEPT_LANGUAGE is defined at http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.4
	 *
	 * @since   11.1
	 */
	public static function detectLanguage()
	{
		static $bestlang;
		static $checked = false;

		if ($checked)
		{
			return $bestlang;
		}

		if (empty($available_languages))
		{
			// Get published Site Languages.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('a.element AS element')
				->from('#__extensions AS a')
				->where('a.type = ' . $db->quote('language'))
				->where('a.client_id = 0')
				->where('a.enabled = 1');
			$db->setQuery($query);
			$available_languages = array_keys((array) $db->loadObjectList('element'));

			// Lowercase $available_languages and populate $available_prefixes
			foreach ($available_languages as $i => $lang)
			{
				$available_languages[$i] = strtolower($lang);
				$available_prefixes[$i] = substr($lang, 0, 2);
			}
		}

		// Read the HTTP-Header
		$http_accept_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';

		preg_match_all("/([[:alpha:]]{1,8})(-([[:alpha:]|-]{1,8}))?" .
					"(\s*;\s*q\s*=\s*(1\.0{0,3}|0\.\d{0,3}))?\s*(,|$)/i",
					$http_accept_language, $hits, PREG_SET_ORDER
					);

		// Default language (in case of no hits) is null
		$bestlang = null;
		$bestqval = 0;

		// Search the best match
		foreach ($hits as $arr)
		{
			// Read data from the array of this hit
			$language = strtolower($arr[1]);
			if (!empty($arr[3]))
			{
				$country_code = strtolower($arr[3]);
				$langprefix = $language;
				$language = $language . "-" . $country_code;
			}

			$qvalue = 1;

			if (!empty($arr[5]))
			{
				$qvalue = floatval($arr[5]);
			}

			// Find q-maximal language
			if (in_array($language, $available_languages, true) && ($qvalue > $bestqval))
			{
				$bestlang = $langprefix . '-' . strtoupper($country_code);
				$bestqval = $qvalue;
			}
			// If no direct hit, try the prefix only but decrease q-value by 10% (as http_negotiate_language() does)
			elseif (in_array($language, $available_prefixes) && (($qvalue * 0.9) > $bestqval))
			{
				// The gotcha here is that in case of possible multiple matches (e.g.: en form en-AU against availables en-US and en-GB)
				// We return the first match (en-US in the example above). No way we can do better, I guess...
				$index = array_search($language, $available_prefixes);
				$bestlang = $available_languages[$index];
				$bestlang = substr($bestlang, 0, 3) . strtoupper(substr($bestlang, 3, 2));
				$bestqval = $qvalue * 0.9;
			}
		}

		$checked = true;
		return $bestlang;
	}

	/**
	 * Get available languages
	 *
	 * @param   string  $key  Array key
	 *
	 * @return  array  An array of published languages
	 *
	 * @since   11.1
	 */
	public static function getLanguages($key = 'default')
	{
		static $languages;

		if (empty($languages))
		{
			// Installation uses available languages
			if (JFactory::getApplication()->getClientId() == 2)
			{
				$languages[$key] = array();
				$knownLangs = JLanguage::getKnownLanguages(JPATH_BASE);

				foreach ($knownLangs as $metadata)
				{
					// Take off 3 letters iso code languages as they can't match browsers' languages and default them to en
					$obj = new stdClass;
					$obj->lang_code = $metadata['tag'];
					$languages[$key][] = $obj;
				}
			}
			else
			{
				$cache = JFactory::getCache('com_languages', '');

				if (!$languages = $cache->get('languages'))
				{
					$db = JFactory::getDbo();
					$query = $db->getQuery(true)
						->select('*')
						->from('#__languages')
						->where('published=1')
						->order('ordering ASC');
					$db->setQuery($query);

					$languages['default'] = $db->loadObjectList();
					$languages['sef'] = array();
					$languages['lang_code'] = array();

					if (isset($languages['default'][0]))
					{
						foreach ($languages['default'] as $lang)
						{
							$languages['sef'][$lang->sef] = $lang;
							$languages['lang_code'][$lang->lang_code] = $lang;
						}
					}

					$cache->store($languages, 'languages');
				}
			}
		}

		return $languages[$key];
	}
}
