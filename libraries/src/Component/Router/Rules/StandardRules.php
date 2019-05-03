<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component\Router\Rules;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\Router\RouterView;

/**
 * Rule for the standard handling of component routing
 *
 * @since  3.4
 */
class StandardRules implements RulesInterface
{
	/**
	 * Router this rule belongs to
	 *
	 * @var    RouterView
	 * @since  3.4
	 */
	protected $router;

	/**
	 * Class constructor.
	 *
	 * @param   RouterView  $router  Router this rule belongs to
	 *
	 * @since   3.4
	 */
	public function __construct(RouterView $router)
	{
		$this->router = $router;
	}

	/**
	 * Dummymethod to fullfill the interface requirements
	 *
	 * @param   array  &$query  The query array to process
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function preprocess(&$query)
	{
	}

	/**
	 * Parse the URL
	 *
	 * @param   array  &$segments  The URL segments to parse
	 * @param   array  &$vars      The vars that result from the segments
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function parse(&$segments, &$vars)
	{
		// Get the views and the currently active query vars
		$views  = $this->router->getViews();
		$active = $this->router->menu->getActive();

		if ($active)
		{
			$vars = array_merge($active->query, $vars);
		}

		// We don't have a view or its not a view of this component! We stop here
		if (!isset($vars['view']) || !isset($views[$vars['view']]))
		{
			return;
		}

		// Copy the segments, so that we can iterate over all of them and at the same time modify the original segments
		$tempSegments = $segments;

		// Iterate over the segments as long as a segment fits
		foreach ($tempSegments as $segment)
		{
			// Our current view is nestable. We need to check first if the segment fits to that
			if ($views[$vars['view']]->nestable)
			{
				if (is_callable(array($this->router, 'get' . ucfirst($views[$vars['view']]->name) . 'Id')))
				{
					$key = call_user_func_array(array($this->router, 'get' . ucfirst($views[$vars['view']]->name) . 'Id'), array($segment, $vars));

					// Did we get a proper key? If not, we need to look in the child-views
					if ($key)
					{
						$vars[$views[$vars['view']]->key] = $key;

						array_shift($segments);

						continue;
					}
				}
				else
				{
					// The router is not complete. The get<View>Id() method is missing.
					return;
				}
			}

			// Lets find the right view that belongs to this segment
			$found = false;

			foreach ($views[$vars['view']]->children as $view)
			{
				if (!$view->key)
				{
					if ($view->name === $segment)
					{
						// The segment is a view name
						$parent       = $views[$vars['view']];
						$vars['view'] = $view->name;
						$found        = true;

						if ($view->parent_key && isset($vars[$parent->key]))
						{
							$parent_key              = $vars[$parent->key];
							$vars[$view->parent_key] = $parent_key;

							unset($vars[$parent->key]);
						}

						break;
					}
				}
				elseif (is_callable(array($this->router, 'get' . ucfirst($view->name) . 'Id')))
				{
					// Hand the data over to the router specific method and see if there is a content item that fits
					$key = call_user_func_array(array($this->router, 'get' . ucfirst($view->name) . 'Id'), array($segment, $vars));

					if ($key)
					{
						// Found the right view and the right item
						$parent       = $views[$vars['view']];
						$vars['view'] = $view->name;
						$found        = true;

						if ($view->parent_key && isset($vars[$parent->key]))
						{
							$parent_key              = $vars[$parent->key];
							$vars[$view->parent_key] = $parent_key;

							unset($vars[$parent->key]);
						}

						$vars[$view->key] = $key;

						break;
					}
				}
			}

			if (!$found)
			{
				return;
			}

			array_shift($segments);
		}
	}

	/**
	 * Build a standard URL
	 *
	 * @param   array  &$query     The vars that should be converted
	 * @param   array  &$segments  The URL segments to create
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function build(&$query, &$segments)
	{
		if (!isset($query['Itemid'], $query['view']))
		{
			return;
		}

		// Get the menu item belonging to the Itemid that has been found
		$item = $this->router->menu->getItem($query['Itemid']);

		if ($item === null
			|| $item->component !== 'com_' . $this->router->getName()
			|| !isset($item->query['view']))
		{
			return;
		}

		// Get menu item layout
		$mLayout = isset($item->query['layout']) ? $item->query['layout'] : null;

		// Get all views for this component
		$views = $this->router->getViews();

		// Return directly when the URL of the Itemid is identical with the URL to build
		if ($item->query['view'] === $query['view'])
		{
			$view = $views[$query['view']];

			if (!$view->key)
			{
				unset($query['view']);

				if (isset($query['layout']) && $mLayout === $query['layout'])
				{
					unset($query['layout']);
				}

				return;
			}

			if (isset($query[$view->key]) && $item->query[$view->key] == (int) $query[$view->key])
			{
				unset($query[$view->key]);

				while ($view)
				{
					unset($query[$view->parent_key]);

					$view = $view->parent;
				}

				unset($query['view']);

				if (isset($query['layout']) && $mLayout === $query['layout'])
				{
					unset($query['layout']);
				}

				return;
			}
		}

		// Get the path from the view of the current URL and parse it to the menu item
		$path  = array_reverse($this->router->getPath($query), true);
		$found = false;

		foreach ($path as $element => $ids)
		{
			$view = $views[$element];

			if ($found === false && $item->query['view'] === $element)
			{
				if ($view->nestable)
				{
					$found = true;
				}
				elseif ($view->children)
				{
					$found = true;

					continue;
				}
			}

			if ($found === false)
			{
				// Jump to the next view
				continue;
			}

			if ($ids)
			{
				if ($view->nestable)
				{
					$found2 = false;

					foreach (array_reverse($ids, true) as $id => $segment)
					{
						if ($found2)
						{
							$segments[] = str_replace(':', '-', $segment);
						}
						elseif ((int) $item->query[$view->key] === (int) $id)
						{
							$found2 = true;
						}
					}
				}
				elseif ($ids === true)
				{
					$segments[] = $element;
				}
				else
				{
					$segments[] = str_replace(':', '-', current($ids));
				}
			}

			if ($view->parent_key)
			{
				// Remove parent key from query
				unset($query[$view->parent_key]);
			}
		}

		if ($found)
		{
			unset($query[$views[$query['view']]->key], $query['view']);

			if (isset($query['layout']) && $mLayout === $query['layout'])
			{
				unset($query['layout']);
			}
		}
	}
}
