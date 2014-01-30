<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JLoader::register('FinderIndexerStemmer', dirname(__DIR__) . '/stemmer.php');

/**
 * Snowball stemmer class for the Finder indexer package.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 * @since       2.5
 */
class FinderIndexerStemmerSnowball extends FinderIndexerStemmer
{
	/**
	 * Method to stem a token and return the root.
	 *
	 * @param   string  $token  The token to stem.
	 * @param   string  $lang   The language of the token.
	 *
	 * @return  string  The root token.
	 *
	 * @since   2.5
	 */
	public function stem($token, $lang)
	{
		// Language to use if All is specified.
		static $defaultLang = '';

		// If language is All then try to get site default language.
		if ($lang == '*' && $defaultLang == '')
		{
			$languages = JLanguageHelper::getLanguages();
			$defaultLang = isset($languages[0]->sef) ? $languages[0]->sef : '*';
			$lang = $defaultLang;
		}

		// Stem the token if it is not in the cache.
		if (!isset($this->cache[$lang][$token]))
		{
			// Get the stem function from the language string.
			switch ($lang)
			{
				// Danish stemmer.
				case 'da':
					$function = 'stem_danish';
					break;

				// German stemmer.
				case 'de':
					$function = 'stem_german';
					break;

				// English stemmer.
				default:
				case 'en':
					$function = 'stem_english';
					break;

				// Spanish stemmer.
				case 'es':
					$function = 'stem_spanish';
					break;

				// Finnish stemmer.
				case 'fi':
					$function = 'stem_finnish';
					break;

				// French stemmer.
				case 'fr':
					$function = 'stem_french';
					break;

				// Hungarian stemmer.
				case 'hu':
					$function = 'stem_hungarian';
					break;

				// Italian stemmer.
				case 'it':
					$function = 'stem_italian';
					break;

				// Norwegian stemmer.
				case 'nb':
					$function = 'stem_norwegian';
					break;

				// Dutch stemmer.
				case 'nl':
					$function = 'stem_dutch';
					break;

				// Portuguese stemmer.
				case 'pt':
					$function = 'stem_portuguese';
					break;

				// Romanian stemmer.
				case 'ro':
					$function = 'stem_romanian';
					break;

				// Russian stemmer.
				case 'ru':
					$function = 'stem_russian_unicode';
					break;

				// Swedish stemmer.
				case 'sv':
					$function = 'stem_swedish';
					break;

				// Turkish stemmer.
				case 'tr':
					$function = 'stem_turkish_unicode';
					break;
			}

			// Stem the word if the stemmer method exists.
			$this->cache[$lang][$token] = function_exists($function) ? $function($token) : $token;
		}

		return $this->cache[$lang][$token];
	}
}
