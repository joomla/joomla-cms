<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Routing class from com_newsfeeds
 *
 * @since  3.3
 */
class NewsfeedsRouter extends JComponentRouterBase
{
	/**
	 * Itemid lookup array
	 * 
	 * @var    array
	 * @since  3.4
	 */
	protected $lookup;

	/**
	 * Find the right Itemid for a com_content article
	 *
	 * @param   array  $query  An associative array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   3.4
	 */
	public function preprocess($query)
	{
		if (isset($query['Itemid']))
		{
			return $query;
		}

		$needles = array();

		if (isset($query['view']))
		{
			if ($query['view'] == 'category')
			{
				if (isset($query['id']))
				{
					$category = JCategories::getInstance('Newsfeeds')->get((int) $query['id']);

					if ($id < 1 || !($category instanceof JCategoryNode))
					{
						$link = '';
					}
					else
					{
						$catids                = array_reverse($category->getPath());
						$needles['category']   = $catids;
						$needles['categories'] = $catids;
					}
				}
			}

			if ($query['view'] == 'newsfeed')
			{
				if (isset($query['id']))
				{
					$needles['newsfeed'] = array($query['id']);
				}

				if (isset($query['catid']) && (int) $query['catid'] > 1)
				{
					$categories = JCategories::getInstance('Newsfeeds');
					$category   = $categories->get((int) $query['catid']);

					if ($category)
					{
						$needles['category']   = array_reverse($category->getPath());
						$needles['categories'] = $needles['category'];
					}
				}
			}
		}

		if (isset($query['lang']) && $query['lang'] != '*')
		{
			$needles['language'] = $query['lang'];
		}

		if ($item = $this->findItem($needles))
		{
			$query['Itemid'] = $item;
		}

		return $query;
	}

	/**
	 * Build the route for the com_newsfeeds component
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   3.3
	 */
	public function build(&$query)
	{
		$segments = array();

		// Get a menu item based on Itemid or currently active
		$params = JComponentHelper::getParams('com_newsfeeds');
		$advanced = $params->get('sef_advanced_link', 0);

		if (empty($query['Itemid']))
		{
			$menuItem = $this->menu->getActive();
		}
		else
		{
			$menuItem = $this->menu->getItem($query['Itemid']);
		}

		$mView = (empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
		$mId   = (empty($menuItem->query['id'])) ? null : $menuItem->query['id'];

		if (isset($query['view']))
		{
			$view = $query['view'];

			if (empty($query['Itemid']) || empty($menuItem) || $menuItem->component != 'com_newsfeeds')
			{
				$segments[] = $query['view'];
			}

			unset($query['view']);
		}

		// Are we dealing with an newsfeed that is attached to a menu item?
		if (isset($query['view']) && ($mView == $query['view']) and (isset($query['id'])) and ($mId == (int) $query['id']))
		{
			unset($query['view']);
			unset($query['catid']);
			unset($query['id']);

			return $segments;
		}

		if (isset($view) and ($view == 'category' or $view == 'newsfeed'))
		{
			if ($mId != (int) $query['id'] || $mView != $view)
			{
				if ($view == 'newsfeed' && isset($query['catid']))
				{
					$catid = $query['catid'];
				}
				elseif (isset($query['id']))
				{
					$catid = $query['id'];
				}

				$menuCatid = $mId;
				$categories = JCategories::getInstance('Newsfeeds');
				$category = $categories->get($catid);

				if ($category)
				{
					$path = $category->getPath();
					$path = array_reverse($path);

					$array = array();

					foreach ($path as $id)
					{
						if ((int) $id == (int) $menuCatid)
						{
							break;
						}

						if ($advanced)
						{
							list($tmp, $id) = explode(':', $id, 2);
						}

						$array[] = $id;
					}

					$segments = array_merge($segments, array_reverse($array));
				}

				if ($view == 'newsfeed')
				{
					if ($advanced)
					{
						list($tmp, $id) = explode(':', $query['id'], 2);
					}
					else
					{
						$id = $query['id'];
					}

					$segments[] = $id;
				}
			}

			unset($query['id']);
			unset($query['catid']);
		}

		if (isset($query['layout']))
		{
			if (!empty($query['Itemid']) && isset($menuItem->query['layout']))
			{
				if ($query['layout'] == $menuItem->query['layout'])
				{
					unset($query['layout']);
				}
			}
			else
			{
				if ($query['layout'] == 'default')
				{
					unset($query['layout']);
				}
			}
		}

		$total = count($segments);

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = str_replace(':', '-', $segments[$i]);
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   3.3
	 */
	public function parse(&$segments)
	{
		$total = count($segments);
		$vars = array();

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
		}

		// Get the active menu item.
		$item	= $this->menu->getActive();
		$params = JComponentHelper::getParams('com_newsfeeds');
		$advanced = $params->get('sef_advanced_link', 0);

		// Count route segments
		$count = count($segments);

		// Standard routing for newsfeeds.
		if (!isset($item))
		{
			$vars['view'] = $segments[0];
			$vars['id']   = $segments[$count - 1];

			return $vars;
		}

		// From the categories view, we can only jump to a category.
		$id = (isset($item->query['id']) && $item->query['id'] > 1) ? $item->query['id'] : 'root';
		$categories = JCategories::getInstance('Newsfeeds')->get($id)->getChildren();
		$vars['catid'] = $id;
		$vars['id'] = $id;
		$found = 0;

		foreach ($segments as $segment)
		{
			$segment = $advanced ? str_replace(':', '-', $segment) : $segment;

			foreach ($categories as $category)
			{
				if ($category->slug == $segment || $category->alias == $segment)
				{
					$vars['id'] = $category->id;
					$vars['catid'] = $category->id;
					$vars['view'] = 'category';
					$categories = $category->getChildren();
					$found = 1;
					break;
				}
			}

			if ($found == 0)
			{
				if ($advanced)
				{
					$db = JFactory::getDbo();
					$query = $db->getQuery(true)
						->select($db->quoteName('id'))
						->from('#__newsfeeds')
						->where($db->quoteName('catid') . ' = ' . (int) $vars['catid'])
						->where($db->quoteName('alias') . ' = ' . $db->quote($segment));
					$db->setQuery($query);
					$nid = $db->loadResult();
				}
				else
				{
					$nid = $segment;
				}

				$vars['id'] = $nid;
				$vars['view'] = 'newsfeed';
			}

			$found = 0;
		}

		return $vars;
	}

	/**
	 * Find an item ID.
	 *
	 * @param   array  $needles  An array of needles to search for.
	 *
	 * @return  mixed  The ID found or null otherwise.
	 *
	 * @since   3.4
	 */
	private function findItem($needles = null)
	{
		$language = isset($needles['language']) ? $needles['language'] : '*';

		// Prepare the reverse lookup array.
		if (!isset($this->lookup[$language]))
		{
			$this->lookup[$language] = array();

			$component  = JComponentHelper::getComponent('com_newsfeeds');

			$attributes = array('component_id');
			$values     = array($component->id);

			if ($language != '*')
			{
				$attributes[] = 'language';
				$values[]     = array($needles['language'], '*');
			}

			$items = $this->menu->getItems($attributes, $values);

			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];

					if (!isset($this->lookup[$language][$view]))
					{
						$this->lookup[$language][$view] = array();
					}

					if (isset($item->query['id']))
					{
						/**
						 * Here it will become a bit tricky
						 * language != * can override existing entries
						 * language == * cannot override existing entries
						 */
						if (!isset($this->lookup[$language][$view][$item->query['id']]) || $item->language != '*')
						{
							$this->lookup[$language][$view][$item->query['id']] = $item->id;
						}
					}
					else
					{
						$this->lookup[$language][$view][0] = $item->id;
					}
				}
			}
		}

		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset($this->lookup[$language][$view]))
				{
					foreach ($ids as $id)
					{
						if (isset($this->lookup[$language][$view][(int) $id]))
						{
							return $this->lookup[$language][$view][(int) $id];
						}
					}
				}
			}
		}

		// Check if the active menuitem matches the requested language
		$active = $this->menu->getActive();

		if ($active
			&& $active->component == 'com_content'
			&& ($language == '*' || in_array($active->language, array('*', $language)) || !JLanguageMultilang::isEnabled()))
		{
			return $active->id;
		}

		// If not found, return language specific home link
		$default = $this->menu->getDefault($language);

		return !empty($default->id) ? $default->id : null;
	}
}

/**
 * newsfeedsBuildRoute
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  &$query  The segments of the URL to parse.
 *
 * @return array
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function newsfeedsBuildRoute(&$query)
{
	$router = new NewsfeedsRouter;

	return $router->build($query);
}

/**
 * newsfeedsParseRoute
 *
 * @param   array  $segments  The segments of the URL to parse.
 *
 * @return array
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function newsfeedsParseRoute($segments)
{
	$router = new NewsfeedsRouter;

	return $router->parse($segments);
}
