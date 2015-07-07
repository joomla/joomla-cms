<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_languages
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');
JLoader::register('MultilangstatusHelper', JPATH_ADMINISTRATOR . '/components/com_languages/helpers/multilangstatus.php');

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
		// Setup data.
		$app = JFactory::getApplication();
		$mode_sef = $app->get('sef', 0);
		$sefs = JLanguageHelper::getLanguages('sef');
		$languages = JLanguageHelper::getLanguages('lang_code');
		$default_lang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		$home_pages = MultilangstatusHelper::getHomepages();
		$levels = JFactory::getUser()->getAuthorisedViewLevels();
		$site_langs = MultilangstatusHelper::getSitelangs();
		$current_lang = JFactory::getLanguage()->getTag();
		$assocs = JLanguageAssociations::isEnabled();

		// Check language access, language is enabled, language folder exists, and language has an Home Page
		foreach ($languages as $lang_code => $language)
		{
			if (($language->access && !in_array($language->access, $levels))
				|| !array_key_exists($lang_code, $site_langs)
				|| !is_dir(JPATH_SITE . '/language/' . $lang_code)
				|| !isset($home_pages[$lang_code]))
			{
				unset($languages[$lang_code]);
			}
		}

		// Get active menu item and check if we are on an home page
		$menu = $app->getMenu();
		$active = $menu->getActive();
		$is_home = false;

		if ($active)
		{
			$active_link = JRoute::_($active->link . '&Itemid=' . $active->id, false);
			$current_link = JUri::getInstance()->toString(array('path', 'query'));

			// Check if we are on the homepage
			$is_home = ($active->home
				&& ($current_link == $active_link
					|| $current_link == '/index.php?lang=' . $languages[$current_lang]->sef
					|| $current_link . 'index.php' == $active_link
					|| $current_link == $active_link . '/'));
		}

		// Load associations
		if ($assocs && !$is_home)
		{
			// Load menu associations
			if ($active)
			{
				$associations = MenusHelper::getAssociations($active->id);
			}

			// Load component associations
			$option = $app->input->get('option');
			$class = ucfirst(str_ireplace('com_', '', $option)) . 'HelperAssociation';
			$cassoc_func = array($class, 'getAssociations');
			JLoader::register($class, JPath::clean(JPATH_COMPONENT_SITE . '/helpers/association.php'));
			if (class_exists($class) && is_callable($cassoc_func))
			{
				$cassociations = call_user_func($cassoc_func);
			}
		}

		// For each language...
		foreach ($languages as $i => &$language)
		{
			$language->active = false;
			switch (true)
			{
				// Home page, SEF
				case ($is_home && $mode_sef):
					$language->link = '/' . $language->sef . '/';
					break;

				// Home page, non-SEF URLs
				case ($is_home && !$mode_sef):
					$language->link = '/index.php?lang=' . $language->sef;
					break;

				// Current language link
				case ($i == $current_lang):
					$language->link = str_replace('&', '&amp;', JUri::getInstance()->toString(array('path', 'query')));
					$language->active = true;
					break;

				// Component association
				case ($assocs && isset($cassociations[$i])):
					$language->link = JRoute::_($cassociations[$i] . '&lang=' . $language->sef);
					break;

				// Menu items association
				// Heads up! "$item = $menu" here below is an assignment, *NOT* comparison
				case ($assocs && isset($associations[$i]) && ($item = $menu->getItem($associations[$i]))):
					$language->link = JRoute::_($item->link . '&Itemid=' . $item->id . '&lang=' . $language->sef);
					break;

				// No association found, SEF mode
				case ($mode_sef):
					$language->link = '/' . $language->sef . '/';
					break;

				// No association found, non-SEF mode
				default:
					$language->link = '/index.php?lang=' . $language->sef;
			}
		}
		return $languages;
	}
}
