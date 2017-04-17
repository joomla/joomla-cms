<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(version_compare(JVERSION, '3.1.4', '>')) {
	class hikashopTagRouteHelper extends JHelperRoute {
		public static function getProductRoute($contentId, $contentCatId, $language) {
			list($id, $name) = explode(':', $contentId, 2);
			if(!include_once(rtrim(JPATH_ADMINISTRATOR,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_hikashop'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php'))
				return;
			$class = hikashop_get('class.product');
			$data = $class->get($id);
			if(empty($link))
				$link = 'index.php?option=com_hikashop&ctrl=product&task=show&cid=' . $id.'&name='.$name;


			$needles = array(
				'layout' => 'listing'
			);
			if(!empty($language) && $language != '*' && JLanguageMultilang::isEnabled()) {
				static::buildLanguageLookup();
				if (isset(static::$lang_lookup[$language])) {
					$link .= '&lang=' . static::$lang_lookup[$language];
					$needles['language'] = $language;
				}
			}

			if ($item = self::lookupItem($needles, 'product')) {
				$link .= '&Itemid=' . $item;
			} elseif(!empty($needles) && $item = self::lookupItem(array(), 'product')) {
				$link .= '&Itemid=' . $item;
			}

			$link = hikashop_contentLink($link,$data,false,true);
			return $link;
		}
		protected static function lookupItem($needles = array(), $view = '') {
			$app = JFactory::getApplication();
			$menus = $app->getMenu('site');
			$language = isset($needles['language']) ? $needles['language'] : '*';

			if (!isset(static::$lookup[$language]))
			{
				static::$lookup[$language] = array();

				$component = JComponentHelper::getComponent('com_hikashop');
				$attributes = array('component_id');
				$values = array($component->id);

				if ($language != '*') {
					$attributes[] = 'language';
					$values[] = array($needles['language'], '*');
				}

				$items = $menus->getItems($attributes, $values);

				foreach ($items as $item) {
					if (isset($item->query) && isset($item->query['view'])) {
						$view = $item->query['view'];
						if (!isset(static::$lookup[$language][$view])){
							static::$lookup[$language][$view] = array();
						}

						if (isset($item->query['layout']))
						{
							if (!isset(static::$lookup[$language][$view][$item->query['layout']]) || $item->language != '*')
							{
								static::$lookup[$language][$view][$item->query['layout']] = $item->id;
							}
						}
					}
				}
			}
			if ($needles) {
				foreach($needles as $layout) {
					if (isset(static::$lookup[$language][$view])) {
						if (isset(static::$lookup[$language][$view][$layout])) {
							return static::$lookup[$language][$view][$layout];
						}
					}
				}
			}
			$active = $menus->getActive();
			if ($active && $active->component == 'com_hikashop' && ($active->language == '*' || !JLanguageMultilang::isEnabled())) {
				return $active->id;
			}
			$default = $menus->getDefault($language);
			return !empty($default->id) ? $default->id : null;
		}
	}
}
