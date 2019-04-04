<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Tags Component Route Helper.
 *
 * @since  3.1
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
				if ($routerMethod === 'getCategoryRoute')
				{
					$link = $routerClass::$routerMethod($contentItemId, $language);
				}
				else
				{
					$link = $routerClass::$routerMethod($contentItemId . ':' . $contentItemAlias, $contentCatId, $language);
				}
			}
		}

		if ($link === '')
		{
			// Create a fallback link in case we can't find the component router
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
			$link = 'index.php?option=com_tags&view=tag&id=' . $id;

			if ($item = self::_findItem($needles))
			{
				$link .= '&Itemid=' . $item;
			}
			else
			{
				$needles = array('tags' => array(1, 0));

				if ($item = self::_findItem($needles))
				{
					$link .= '&Itemid=' . $item;
				}
			}
		}

		return $link;
	}

	/**
	 * Tries to load the router for the tags view.
	 *
	 * @return  string  URL link to pass to JRoute
	 *
	 * @since   3.7
	 */
	public static function getTagsRoute()
	{
		$needles = array(
			'tags'  => array(0)
		);

		$link = 'index.php?option=com_tags&view=tags';

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	/**
	 * Find Item static function
	 *
	 * @param   array  $needles  Array used to get the language value
	 *
	 * @return null
	 *
	 * @throws Exception
	 */
	protected static function _findItem($needles = null)
	{
		$app      = JFactory::getApplication();
		$menus    = $app->getMenu('site');
		$language = isset($needles['language']) ? $needles['language'] : '*';

		// Prepare the reverse lookup array.
		if (self::$lookup === null)
		{
			self::$lookup = array();

			$component = JComponentHelper::getComponent('com_tags');
			$items     = $menus->getItems('component_id', $component->id);

			if ($items)
			{
				foreach ($items as $item)
				{
					if (isset($item->query, $item->query['view']))
					{
						$lang = ($item->language != '' ? $item->language : '*');

						if (!isset(self::$lookup[$lang]))
						{
							self::$lookup[$lang] = array();
						}

						$view = $item->query['view'];

						if (!isset(self::$lookup[$lang][$view]))
						{
							self::$lookup[$lang][$view] = array();
						}

						// Only match menu items that list one tag
						if (isset($item->query['id']) && is_array($item->query['id']))
						{
							foreach ($item->query['id'] as $position => $tagId)
							{
								if (!isset(self::$lookup[$lang][$view][$item->query['id'][$position]]) || count($item->query['id']) == 1)
								{
									self::$lookup[$lang][$view][$item->query['id'][$position]] = $item->id;
								}
							}
						}
						elseif ($view == 'tags')
						{
							self::$lookup[$lang]['tags'][] = $item->id;
						}
					}
				}
			}
		}

		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$language][$view]))
				{
					foreach ($ids as $id)
					{
						if (isset(self::$lookup[$language][$view][(int) $id]))
						{
							return self::$lookup[$language][$view][(int) $id];
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
