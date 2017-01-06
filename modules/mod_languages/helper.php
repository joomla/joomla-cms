<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_languages
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');

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
		$user		= JFactory::getUser();
		$lang		= JFactory::getLanguage();
		$languages	= JLanguageHelper::getLanguages();
		$app		= JFactory::getApplication();
		$menu		= $app->getMenu();
		$active		= $menu->getActive();

		// Get menu home items
		$homes = array();
		$homes['*'] = $menu->getDefault('*');

		foreach ($languages as $item)
		{
			$default = $menu->getDefault($item->lang_code);

			if ($default && $default->language === $item->lang_code)
			{
				$homes[$item->lang_code] = $default;
			}
		}

		// Load associations
		$assoc = JLanguageAssociations::isEnabled();

		if ($assoc)
		{
			if ($active)
			{
				$associations = MenusHelper::getAssociations($active->id);
			}

			// Load component associations
			$class = str_replace('com_', '', $app->input->get('option')) . 'HelperAssociation';
			JLoader::register($class, JPATH_COMPONENT_SITE . '/helpers/association.php');

			if (class_exists($class) && is_callable(array($class, 'getAssociations')))
			{
				$cassociations = call_user_func(array($class, 'getAssociations'));
			}
		}

		$levels    = $user->getAuthorisedViewLevels();
		$sitelangs = JLanguageHelper::getInstalledLanguages(0);
		$multilang = JLanguageMultilang::isEnabled();

		// Filter allowed languages
		foreach ($languages as $i => &$language)
		{
			// Do not display language without frontend UI
			if (!array_key_exists($language->lang_code, $sitelangs))
			{
				unset($languages[$i]);
			}
			// Do not display language without specific home menu
			elseif (!isset($homes[$language->lang_code]))
			{
				unset($languages[$i]);
			}
			// Do not display language without authorized access level
			elseif (isset($language->access) && $language->access && !in_array($language->access, $levels))
			{
				unset($languages[$i]);
			}
			else
			{
				$language->active = ($language->lang_code === $lang->getTag());

				// Fetch language rtl
				// If loaded language get from current JLanguage metadata
				if ($language->active)
				{
					$language->rtl = $lang->isRtl();
				}
				// If not loaded language fetch metadata directly for performance
				else
				{
					$languageMetadata = JLanguageHelper::getMetadata($language->lang_code);
					$language->rtl    = $languageMetadata['rtl'];
				}

				if ($multilang)
				{
					if (isset($cassociations[$language->lang_code]))
					{
						$language->link = JRoute::_($cassociations[$language->lang_code] . '&lang=' . $language->sef);
					}
					elseif (isset($associations[$language->lang_code]) && $menu->getItem($associations[$language->lang_code]))
					{
						$itemid = $associations[$language->lang_code];
						$language->link = JRoute::_('index.php?lang=' . $language->sef . '&Itemid=' . $itemid);
					}
					elseif ($active && $active->language == '*')
					{
						$language->link = JRoute::_('index.php?lang=' . $language->sef . '&Itemid=' . $active->id);
					}
					else
					{
						if ($language->active)
						{
							$language->link = JUri::getInstance()->toString(array('path', 'query'));
						}
						else
						{
							$itemid = isset($homes[$language->lang_code]) ? $homes[$language->lang_code]->id : $homes['*']->id;
							$language->link = JRoute::_('index.php?lang=' . $language->sef . '&Itemid=' . $itemid);
						}
					}
				}
				else
				{
					$language->link = JRoute::_('&Itemid=' . $homes['*']->id);
				}
			}
		}

		return $languages;
	}
}
