<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Menu\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Factory;

/**
 * Helper for mod_menu
 *
 * @package     Joomla.Site
 * @subpackage  mod_menu
 * @since       1.5
 */
class MenuHelper
{
	/**
	 * Get a list of the menu items.
	 *
	 * @param   \Joomla\Registry\Registry  &$params  The module options.
	 *
	 * @return  array
	 *
	 * @since   1.5
	 */
	public static function getList(&$params)
	{
		$menu = Factory::getApplication()->getMenu();

		// Get active menu item
		$base   = self::getBase($params);
		$levels = Factory::getUser()->getAuthorisedViewLevels();
		asort($levels);
		$key    = 'menu_items' . $params . implode(',', $levels) . '.' . $base->id;
		$cache  = Factory::getCache('mod_menu', '');

		if ($cache->contains($key))
		{
			$items = $cache->get($key);
		}
		else
		{
			$path           = $base->tree;
			$start          = (int) $params->get('startLevel');
			$end            = (int) $params->get('endLevel');
			$showAll        = $params->get('showAllChildren');
			$items          = $menu->getItems('menutype', $params->get('menutype'));
			$hidden_parents = array();
			$lastitem       = 0;

			if ($items)
			{
				foreach ($items as $i => $item)
				{
					$item->parent = false;

					if (isset($items[$lastitem]) && $items[$lastitem]->id == $item->parent_id && $item->params->get('menu_show', 1) == 1)
					{
						$items[$lastitem]->parent = true;
					}

					if (($start && $start > $item->level)
						|| ($end && $item->level > $end)
						|| (!$showAll && $item->level > 1 && !in_array($item->parent_id, $path))
						|| ($start > 1 && !in_array($item->tree[$start - 2], $path)))
					{
						unset($items[$i]);
						continue;
					}

					// Exclude item with menu item option set to exclude from menu modules
					if (($item->params->get('menu_show', 1) == 0) || in_array($item->parent_id, $hidden_parents))
					{
						$hidden_parents[] = $item->id;
						unset($items[$i]);
						continue;
					}

					$item->deeper     = false;
					$item->shallower  = false;
					$item->level_diff = 0;

					if (isset($items[$lastitem]))
					{
						$items[$lastitem]->deeper     = ($item->level > $items[$lastitem]->level);
						$items[$lastitem]->shallower  = ($item->level < $items[$lastitem]->level);
						$items[$lastitem]->level_diff = ($items[$lastitem]->level - $item->level);
					}

					$lastitem     = $i;
					$item->active = false;
					$item->flink  = $item->link;

					// Reverted back for CMS version 2.5.6
					switch ($item->type)
					{
						case 'separator':
							break;

						case 'heading':
							// No further action needed.
							break;

						case 'url':
							if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false))
							{
								// If this is an internal Joomla link, ensure the Itemid is set.
								$item->flink = $item->link . '&Itemid=' . $item->id;
							}
							break;

						case 'alias':
							$item->flink = 'index.php?Itemid=' . $item->params->get('aliasoptions');
							break;

						default:
							$item->flink = 'index.php?Itemid=' . $item->id;
							break;
					}

					if ((strpos($item->flink, 'index.php?') !== false) && strcasecmp(substr($item->flink, 0, 4), 'http'))
					{
						$item->flink = \JRoute::_($item->flink, true, $item->params->get('secure'));
					}
					else
					{
						$item->flink = \JRoute::_($item->flink);
					}

					// We prevent the double encoding because for some reason the $item is shared for menu modules and we get double encoding
					// when the cause of that is found the argument should be removed
					$item->title        = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false);
					$item->anchor_css   = htmlspecialchars($item->params->get('menu-anchor_css', ''), ENT_COMPAT, 'UTF-8', false);
					$item->anchor_title = htmlspecialchars($item->params->get('menu-anchor_title', ''), ENT_COMPAT, 'UTF-8', false);
					$item->anchor_rel   = htmlspecialchars($item->params->get('menu-anchor_rel', ''), ENT_COMPAT, 'UTF-8', false);
					$item->menu_image   = $item->params->get('menu_image', '') ?
						htmlspecialchars($item->params->get('menu_image', ''), ENT_COMPAT, 'UTF-8', false) : '';
				}

				if (isset($items[$lastitem]))
				{
					$items[$lastitem]->deeper     = (($start ?: 1) > $items[$lastitem]->level);
					$items[$lastitem]->shallower  = (($start ?: 1) < $items[$lastitem]->level);
					$items[$lastitem]->level_diff = ($items[$lastitem]->level - ($start ?: 1));
				}
			}

			$cache->store($items, $key);
		}

		return $items;
	}

	/**
	 * Get base menu item.
	 *
	 * @param   \Joomla\Registry\Registry  &$params  The module options.
	 *
	 * @return  object
	 *
	 * @since	3.0.2
	 */
	public static function getBase(&$params)
	{
		// Get base menu item from parameters
		if ($params->get('base'))
		{
			$base = Factory::getApplication()->getMenu()->getItem($params->get('base'));
		}
		else
		{
			$base = false;
		}

		// Use active menu item if no base found
		if (!$base)
		{
			$base = self::getActive($params);
		}

		return $base;
	}

	/**
	 * Get active menu item.
	 *
	 * @param   \Joomla\Registry\Registry  &$params  The module options.
	 *
	 * @return  object
	 *
	 * @since	3.0.2
	 */
	public static function getActive(&$params)
	{
		$menu = Factory::getApplication()->getMenu();

		return $menu->getActive() ?: self::getDefault();
	}

	/**
	 * Get default menu item (home page) for current language.
	 *
	 * @return  object
	 */
	public static function getDefault()
	{
		$menu = Factory::getApplication()->getMenu();

		// Look for the home menu
		if (Multilanguage::isEnabled())
		{
			return $menu->getDefault(Factory::getLanguage()->getTag());
		}

		return $menu->getDefault();
	}
}
