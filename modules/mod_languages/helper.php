<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_languages
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');

/**
 * Helper for mod_languages
 *
 * @package     Joomla.Site
 * @subpackage  mod_languages
 */
abstract class modLanguagesHelper
{
	public static function getList(&$params)
	{
		$user	= JFactory::getUser();
		$lang 	= JFactory::getLanguage();
		$app	= JFactory::getApplication();
		$menu 	= $app->getMenu();

		// Get menu home items
		$homes = array();
		foreach($menu->getMenu() as $item) {
			if ($item->home) {
				$homes[$item->language] = $item;
			}
		}

		// Load associations
		$assoc = isset($app->menu_associations) ? $app->menu_associations : 0;
		if ($assoc)
		{
			$active = $menu->getActive();
			if ($active)
			{
				$associations = MenusHelper::getAssociations($active->id);
			}
		}

		$levels		= $user->getAuthorisedViewLevels();
		$languages	= JLanguageHelper::getLanguages();

		// Filter allowed languages
		foreach($languages as $i => &$language) {

			// Do not display language without frontend UI
			if (!JLanguage::exists($language->lang_code)) {
				unset($languages[$i]);
			}
			// Do not display language without specific home menu
			elseif (!isset($homes[$language->lang_code])) {
				unset($languages[$i]);
			}
			// Do not display language without authorized access level
			elseif (isset($language->access) && $language->access && !in_array($language->access, $levels)) {
				unset($languages[$i]);
			}
			else {
				$language->active = $language->lang_code == $lang->getTag();
				if ($app->getLanguageFilter()) {
					if (isset($associations[$language->lang_code]) && $menu->getItem($associations[$language->lang_code])) {
						$itemid = $associations[$language->lang_code];
						if ($app->getCfg('sef') == '1')
						{
							$language->link = JRoute::_('index.php?lang='.$language->sef.'&Itemid='.$itemid);
						}
						else {
							$language->link = 'index.php?lang='.$language->sef.'&amp;Itemid='.$itemid;
						}
					}
					else {
						if ($app->getCfg('sef') == '1')
						{
							$itemid = isset($homes[$language->lang_code]) ? $homes[$language->lang_code]->id : $homes['*']->id;
							$language->link = JRoute::_('index.php?lang='.$language->sef.'&Itemid='.$itemid);
						}
						else {
							$language->link = 'index.php?lang='.$language->sef;
						}
					}
				}
				else {
					$language->link = JRoute::_('&Itemid='.$homes['*']->id);
				}
			}
		}
		return $languages;
	}
}
