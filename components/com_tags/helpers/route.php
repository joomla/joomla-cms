<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Tags Component Route Helper
 *
 * @static
 * @package     Joomla.Site
 * @subpackage  com_tags
 * @since       3.1
 */
class TagsHelperRoute extends JHelperRoute
{
	protected static $lookup;

	/**
	 * Tries to load the router for the component and calls it. Otherwise uses getTagRoute.
	 *
	 * @param   integer  $contentItemId     Component item id
	 * @param   string   $contentItemAlias  Component item alias
	 * @param   integer  $contentCatId      Component item category id
	 * @param   string   $language          Component item language
	 * @param   string   $typeAlias         Component type alias
	 * @param   string   $routerName        Component router
	 *
	 * @return  string  URL link to pass to JRoute
	 *
	 * @since   3.1
	 */
	public static function getItemRoute($contentItemId, $contentItemAlias, $contentCatId, $language, $typeAlias, $routerName)
	{
		$link = '';
		$explodedAlias = explode('.', $typeAlias);
		$explodedRouter = explode('::', $routerName);
		if (file_exists($routerFile = JPATH_BASE . '/components/' . $explodedAlias[0] . '/helpers/route.php'))
		{
			JLoader::register($explodedRouter[0], $routerFile);
			$routerClass = $explodedRouter[0];
			$routerMethod = $explodedRouter[1];
			if (class_exists($routerClass) && method_exists($routerClass, $routerMethod))
			{
				if ($routerMethod == 'getCategoryRoute')
				{
					$link = $routerClass::$routerMethod($contentItemId, $language);
				}
				else
				{
					$link = $routerClass::$routerMethod($contentItemId . ':' . $contentItemAlias, $contentCatId, $language);
				}
			}
		}
		if ($link == '')
		{
			// create a fallback link in case we can't find the component router
			$router = new JHelperRoute;
			$link = $router->getRoute($contentItemId, $typeAlias, $link, $language, $contentCatId);
		}
		return $link;
	}

	/**
	 * Tries to load the router for the component and calls it. Otherwise calls getRoute.
	 *
	 * @param   integer  $id  The ID of the tag
	 *
	 * @return  string  URL link to pass to JRoute
	 *
	 * @since   3.1
	 */
	public static function getTagRoute($id)
	{
		$needles = array(
			'tag'  => array((int) $id)
		);
		if ($id < 1)
		{
			$link = '';
		}
		else
		{
			if (!empty($needles) && $item = self::_findItem($needles))
			{
				$link = 'index.php?Itemid=' . $item;
			}
			else
			{
				// Create the link
				$link = 'index.php?option=com_tags&view=tag&id=' . $id;
			}
		}

		return $link;
	}

	protected static function _findItem($needles = null)
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');
		$language	= isset($needles['language']) ? $needles['language'] : '*';

		// Prepare the reverse lookup array.
		if (self::$lookup === null)
		{
			self::$lookup = array();

			$component	= JComponentHelper::getComponent('com_tags');
			$items		= $menus->getItems('component_id', $component->id);

			if ($items) {
				foreach ($items as $item)
				{
					if (isset($item->query) && isset($item->query['view']))
					{
						$view = $item->query['view'];

						if (!isset(self::$lookup[$view]))
						{
							self::$lookup[$view] = array();
						}

						// Only match menu items that list one tag
						if (isset($item->query['id'][0]) && count($item->query['id']) == 1)
						{
							// Here it will become a bit tricky
							// language != * can override existing entries
							// language == * cannot override existing entries
							if (!isset(self::$lookup[$language][$view][$item->query['id'][0]]) || $item->language != '*')
							{
								self::$lookup[$language][$view][$item->query['id'][0]] = $item->id;
							}

							self::$lookup[$view][$item->query['id'][0]] = $item->id;
						}
						if (isset($item->query["tag_list_language_filter"]) && $item->query["tag_list_language_filter"] != '')
						{
							$language = $item->query["tag_list_language_filter"];
						}
					}
				}
			}
		}

		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$view]))
				{
					foreach($ids as $id)
					{
						if (isset(self::$lookup[$view][(int) $id]))
						{
							return self::$lookup[$view][(int) $id];
						}
					}
				}
			}
		}
		else
		{
			$active = $menus->getActive();
			if ($active)
			{
				return $active->id;
			}
		}

		return null;
	}
}
