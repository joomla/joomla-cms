<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_languages
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_languages
 *
 * @package     Joomla.Site
 * @subpackage  mod_languages
 *
 * @since       1.6.0
 */
abstract class ModLanguagesHelper
{
	/**
	 * Gets a list of available languages
	 *
	 * @param   \Joomla\Registry\Registry  &$params  module params
	 *
	 * @return  array
	 */
	public static function getList(&$params)
	{
		// If multilanguage is not active (language filter disabled) return an empty array.
		if (!JLanguageMultilang::isEnabled())
		{
			return array();
		}

		// There can also be no languages if language filter is published but no languages are available.
		$languages = JLanguageMultilang::getAvailableLanguages();
		if (count($languages) == 0)
		{
			return array();
		}

		// Prefetch variables
		$lang      = JFactory::getLanguage();
		$langTag   = $lang->getTag();
		$langRtl   = $lang->isRtl();

		// If there are languages, load the association links.
		$associationLinks = JLanguageAssociations::getAssociationsLinks(true);

		// Get the if language is rtl and the association link for each language.
		foreach ($languages as $i => $language)
		{
			$language->active = ($language->lang_code == $langTag);

			// If language already loaded language get the rtl from current JLanguage metadata.
			if ($language->active)
			{
				$language->rtl = $langRtl;
			}
			// If language not loaded fetch rtl from metadata directly for performance.
			else
			{
				$languageMetadata = JLanguage::getMetadata($language->lang_code);
				$language->rtl    = $languageMetadata['rtl'];
			}

			$language->link = $associationLinks[$language->lang_code];
		}

		return $languages;
	}
}
