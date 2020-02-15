<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Legacy routing rules class from com_content
 *
 * @since       3.6
 * @deprecated  4.0
 */
class ContentRouterRulesLegacy implements JComponentRouterRulesInterface
{
	/**
	 * Constructor for this legacy router
	 *
	 * @param   JComponentRouterView  $router  The router this rule belongs to
	 *
	 * @since       3.6
	 * @deprecated  4.0
	 */
	public function __construct($router)
	{
		$this->router = $router;
	}

	/**
	 * Preprocess the route for the com_content component
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  void
	 *
	 * @since       3.6
	 * @deprecated  4.0
	 */
	public function preprocess(&$query)
	{
	}

	/**
	 * Build the route for the com_content component
	 *
	 * @param   array  &$query     An array of URL arguments
	 * @param   array  &$segments  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @return  void
	 *
	 * @since       3.6
	 * @deprecated  4.0
	 */
	public function build(&$query, &$segments)
	{
		// Get a menu item based on Itemid or currently active
		$params = JComponentHelper::getParams('com_content');
		$advanced = $params->get('sef_advanced_link', 0);

		// We need a menu item.  Either the one specified in the query, or the current active one if none specified
		if (empty($query['Itemid']))
		{
			$menuItem = $this->router->menu->getActive();
			$menuItemGiven = false;
		}
		else
		{
			$menuItem = $this->router->menu->getItem($query['Itemid']);
			$menuItemGiven = true;
		}

		// Check again
		if ($menuItemGiven && isset($menuItem) && $menuItem->component != 'com_content')
		{
			$menuItemGiven = false;
			unset($query['Itemid']);
		}

		if (isset($query['view']))
		{
			$view = $query['view'];
		}
		else
		{
			// We need to have a view in the query or it is an invalid URL
			return;
		}

		// Are we dealing with an article or category that is attached to a menu item?
		if ($menuItem !== null
			&& isset($menuItem->query['view'], $query['view'], $menuItem->query['id'], $query['id'])
			&& $menuItem->query['view'] == $query['view']
			&& $menuItem->query['id'] == (int) $query['id'])
		{
			unset($query['view']);

			if (isset($query['catid']))
			{
				unset($query['catid']);
			}

			if (isset($query['layout']))
			{
				unset($query['layout']);
			}

			unset($query['id']);

			return;
		}

		if ($view == 'category' || $view == 'article')
		{
			if (!$menuItemGiven)
			{
				$segments[] = $view;
			}

			unset($query['view']);

			if ($view == 'article')
			{
				if (isset($query['id']) && isset($query['catid']) && $query['catid'])
				{
					$catid = $query['catid'];

					// Make sure we have the id and the alias
					if (strpos($query['id'], ':') === false)
					{
						$db = JFactory::getDbo();
						$dbQuery = $db->getQuery(true)
							->select('alias')
							->from('#__content')
							->where('id=' . (int) $query['id']);
						$db->setQuery($dbQuery);
						$alias = $db->loadResult();
						$query['id'] = $query['id'] . ':' . $alias;
					}
				}
				else
				{
					// We should have these two set for this view.  If we don't, it is an error
					return;
				}
			}
			else
			{
				if (isset($query['id']))
				{
					$catid = $query['id'];
				}
				else
				{
					// We should have id set for this view.  If we don't, it is an error
					return;
				}
			}

			if ($menuItemGiven && isset($menuItem->query['id']))
			{
				$mCatid = $menuItem->query['id'];
			}
			else
			{
				$mCatid = 0;
			}

			$categories = JCategories::getInstance('Content');
			$category = $categories->get($catid);

			if (!$category)
			{
				// We couldn't find the category we were given.  Bail.
				return;
			}

			$path = array_reverse($category->getPath());

			$array = array();

			foreach ($path as $id)
			{
				if ((int) $id == (int) $mCatid)
				{
					break;
				}

				list($tmp, $id) = explode(':', $id, 2);

				$array[] = $id;
			}

			$array = array_reverse($array);

			if (!$advanced && count($array))
			{
				$array[0] = (int) $catid . ':' . $array[0];
			}

			$segments = array_merge($segments, $array);

			if ($view == 'article')
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

			unset($query['id'], $query['catid']);
		}

		if ($view == 'archive')
		{
			if (!$menuItemGiven)
			{
				$segments[] = $view;
				unset($query['view']);
			}

			if (isset($query['year']))
			{
				if ($menuItemGiven)
				{
					$segments[] = $query['year'];
					unset($query['year']);
				}
			}

			if (isset($query['year']) && isset($query['month']))
			{
				if ($menuItemGiven)
				{
					$segments[] = $query['month'];
					unset($query['month']);
				}
			}
		}

		if ($view == 'featured')
		{
			if (!$menuItemGiven)
			{
				$segments[] = $view;
			}

			unset($query['view']);
		}

		/*
		 * If the layout is specified and it is the same as the layout in the menu item, we
		 * unset it so it doesn't go into the query string.
		 */
		if (isset($query['layout']))
		{
			if ($menuItemGiven && isset($menuItem->query['layout']))
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
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 * @param   array  &$vars      The URL attributes to be used by the application.
	 *
	 * @return  void
	 *
	 * @since       3.6
	 * @deprecated  4.0
	 */
	public function parse(&$segments, &$vars)
	{
		$total = count($segments);

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
		}

		// Get the active menu item.
		$item = $this->router->menu->getActive();
		$params = JComponentHelper::getParams('com_content');
		$advanced = $params->get('sef_advanced_link', 0);
		$db = JFactory::getDbo();

		// Count route segments
		$count = count($segments);

		/*
		 * Standard routing for articles.  If we don't pick up an Itemid then we get the view from the segments
		 * the first segment is the view and the last segment is the id of the article or category.
		 */
		if (!isset($item))
		{
			$vars['view'] = $segments[0];
			$vars['id'] = $segments[$count - 1];

			return;
		}

		/*
		 * If there is only one segment, then it points to either an article or a category.
		 * We test it first to see if it is a category.  If the id and alias match a category,
		 * then we assume it is a category.  If they don't we assume it is an article
		 */
		if ($count == 1)
		{
			// We check to see if an alias is given.  If not, we assume it is an article
			if (strpos($segments[0], ':') === false)
			{
				$vars['view'] = 'article';
				$vars['id'] = (int) $segments[0];

				return;
			}

			list($id, $alias) = explode(':', $segments[0], 2);

			// First we check if it is a category
			$category = JCategories::getInstance('Content')->get($id);

			if ($category && $category->alias == $alias)
			{
				$vars['view'] = 'category';
				$vars['id'] = $id;

				return;
			}
			else
			{
				$query = $db->getQuery(true)
					->select($db->quoteName(array('alias', 'catid')))
					->from($db->quoteName('#__content'))
					->where($db->quoteName('id') . ' = ' . (int) $id);
				$db->setQuery($query);
				$article = $db->loadObject();

				if ($article)
				{
					if ($article->alias == $alias)
					{
						$vars['view'] = 'article';
						$vars['catid'] = (int) $article->catid;
						$vars['id'] = (int) $id;

						return;
					}
				}
			}
		}

		/*
		 * If there was more than one segment, then we can determine where the URL points to
		 * because the first segment will have the target category id prepended to it.  If the
		 * last segment has a number prepended, it is an article, otherwise, it is a category.
		 */
		if (!$advanced)
		{
			$cat_id = (int) $segments[0];

			$article_id = (int) $segments[$count - 1];

			if ($article_id > 0)
			{
				$vars['view'] = 'article';
				$vars['catid'] = $cat_id;
				$vars['id'] = $article_id;
			}
			else
			{
				$vars['view'] = 'category';
				$vars['id'] = $cat_id;
			}

			return;
		}

		// We get the category id from the menu item and search from there
		$id = $item->query['id'];
		$category = JCategories::getInstance('Content')->get($id);

		if (!$category)
		{
			JError::raiseError(404, JText::_('COM_CONTENT_ERROR_PARENT_CATEGORY_NOT_FOUND'));

			return;
		}

		$categories = $category->getChildren();
		$vars['catid'] = $id;
		$vars['id'] = $id;
		$found = 0;

		foreach ($segments as $segment)
		{
			$segment = str_replace(':', '-', $segment);

			foreach ($categories as $category)
			{
				if ($category->alias == $segment)
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
						->from('#__content')
						->where($db->quoteName('catid') . ' = ' . (int) $vars['catid'])
						->where($db->quoteName('alias') . ' = ' . $db->quote($segment));
					$db->setQuery($query);
					$cid = $db->loadResult();
				}
				else
				{
					$cid = $segment;
				}

				$vars['id'] = $cid;

				if ($item->query['view'] == 'archive' && $count != 1)
				{
					$vars['year'] = $count >= 2 ? $segments[$count - 2] : null;
					$vars['month'] = $segments[$count - 1];
					$vars['view'] = 'archive';
				}
				else
				{
					$vars['view'] = 'article';
				}
			}

			$found = 0;
		}
	}
}
