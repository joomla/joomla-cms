<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Rule for the standard handling of component routing
 *
 * @since  3.4
 */
class JComponentRouterRulesStandard implements JComponentRouterRulesInterface
{
	/**
	 * Router this rule belongs to
	 *
	 * @var JComponentRouterView
	 * @since 3.4
	 */
	protected $router;

	/**
	 * Class constructor.
	 *
	 * @param   JComponentRouterView  $router  Router this rule belongs to
	 *
	 * @since   3.4
	 */
	public function __construct(JComponentRouterView $router)
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
		$views = $this->router->getViews();
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
		$temp_segments = $segments;

		// Iterate over the segments as long as a segment fits
		foreach ($temp_segments as $segment)
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
					// The router is not complete. The get<View>Key() method is missing.
					return;
				}
			}

			// Lets find the right view that belongs to this segment
			$found = false;
			foreach ($views[$vars['view']]->children as $view)
			{
				if (!$view->key)
				{
					if ($view->name == $segment)
					{
						// The segment is a view name
						$parent = $views[$vars['view']];
						$vars['view'] = $view->name;
						$found = true;

						if ($view->parent_key && isset($vars[$parent->key]))
						{
							$parent_key = $vars[$parent->key];
							unset($vars[$parent->key]);
							$vars[$view->parent_key] = $parent_key;
						}

						break;
					}
				}
				else
				{
					if (is_callable(array($this->router, 'get' . ucfirst($view->name) . 'Id')))
					{
						// Hand the data over to the router specific method and see if there is a content item that fits
						$key = call_user_func_array(array($this->router, 'get' . ucfirst($view->name) . 'Id'), array($segment, $vars));

						if ($key)
						{
							// Found the right view and the right item
							$parent = $views[$vars['view']];
							$vars['view'] = $view->name;
							$found = true;

							if ($view->parent_key && isset($vars[$parent->key]))
							{
								$parent_key = $vars[$parent->key];
								unset($vars[$parent->key]);
								$vars[$view->parent_key] = $parent_key;
							}

							$vars[$view->key] = $key;

							break;
						}
					}
				}
			}

			if (!$found)
			{
				return;
			}
			else
			{
				array_shift($segments);
			}
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
		// Get the menu item belonging to the Itemid that has been found
		$item = $this->router->menu->getItem($query['Itemid']);

		if (!isset($query['view']))
		{
			return;
		}

		// Get all views for this component
		$views = $this->router->getViews();

		// Return directly when the URL of the Itemid is identical with the URL to build
		if (isset($item->query['view']) && $item->query['view'] == $query['view'])
		{
			$view = $views[$query['view']];

			if (isset($item->query[$view->key]) && $item->query[$view->key] == (int) $query[$view->key])
			{
				unset($query[$view->key]);
				while ($view)
				{
					unset($query[$view->parent_key]);
					$view = $view->parent;
				}
				unset($query['view']);

				if (isset($item->query['layout']) && isset($query['layout']) && $item->query['layout'] == $query['layout'])
				{
					unset($query['layout']);
				}

				return;
			}

			if (!$view->key)
			{
				if (isset($item->query['layout']) && isset($query['layout']) && $item->query['layout'] == $query['layout'])
				{
					unset($query['view']);
					unset($query['layout']);
					return;
				}
			}
		}

		// Get the path from the view of the current URL and parse it to the menu item
		$path = array_reverse($this->router->getPath($query), true);
		$found = false;
		$found2 = false;
		for ($i = 0, $j = count($path); $i < $j; $i++)
		{
			reset($path);
			$view = key($path);
			if ($found)
			{
				$ids = array_shift($path);
				if ($views[$view]->nestable)
				{
					foreach (array_reverse($ids, true) as $id => $segment)
					{
						if ($found2)
						{
							$segments[] = str_replace(':', '-', $segment);
						}
						else
						{
							if ((int) $item->query[$views[$view]->key] == (int) $id)
							{
								$found2 = true;
							}
						}
					}
				}
				else
				{
					if (is_bool($ids))
					{
						$segments[] = $views[$view]->name;
					}
					else
					{
						$segments[] = str_replace(':', '-', array_shift($ids));
					}
				}
			}
			else
			{
				if ($item->query['view'] != $view)
				{
					array_shift($path);
				}
				else
				{
					if (!$views[$view]->nestable)
					{
						array_shift($path);
					}
					else
					{
						$i--;
						$found2 = false;
					}

					if (count($views[$view]->children))
					{
						$found = true;
					}
				}
			}
			unset($query[$views[$view]->parent_key]);
		}

		if ($found)
		{
			unset($query['layout']);
			unset($query[$views[$query['view']]->key]);
			unset($query['view']);
		}
	}
}
