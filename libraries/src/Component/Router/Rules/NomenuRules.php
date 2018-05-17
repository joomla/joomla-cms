<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component\Router\Rules;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\Router\RouterView;

/**
 * Rule to process URLs without a menu item
 *
 * @since  3.4
 */
class NomenuRules implements RulesInterface
{
	/**
	 * Router this rule belongs to
	 *
	 * @var RouterView
	 * @since 3.4
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
	 * @codeCoverageIgnore
	 */
	public function preprocess(&$query)
	{
	}

	/**
	 * Parse a menu-less URL
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
		$active = $this->router->menu->getActive();

		if ($active !== null)
		{
			return;
		}

		$views = $this->router->getViews();

		if (!isset($views[$segments[0]]))
		{
			return;
		}

		$vars['view'] = array_shift($segments);
		$mainView     = $views[$vars['view']];

		// Create a temporary copy of vars to use in get<View>Id
		$vars2 = $vars;

		// Total views in path
		$totalViews = count($mainView->path);

		foreach ($mainView->path as $i => $element)
		{
			if (!$segments)
			{
				// Wrong URL, some segment missing
				return;
			}

			$view = $views[$element];

			if ($element !== $vars['view'] && $view->key)
			{
				$child = $views[$mainView->path[$i+1]];

				if ($child->nestable && $view->key === $child->key)
				{
					// Do not process this view, child will do it as they work on the same path elements
					continue;
				}
			}

			// Remember parent key value from parent view
			$parentId = $view->parent_key && isset($vars2[$view->parent->key]) ? $vars2[$view->parent->key] : null;

			// Generate function name
			$func = array($this->router, 'get' . ucfirst($view->name) . 'Id');

			while ($segments)
			{
				if ($view->nestable)
				{
					// Limit number of calls to get<View>Id()
					if (count($segments) + $i >= $totalViews)
					{
						// If query has no key set, we assume 0.
						if (!isset($vars2[$view->key]))
						{
							$vars2[$view->key] = 0;
						}

						// Required for noIDs to get id from alias
						if (is_callable($func))
						{
							$key = call_user_func_array($func, array($segments[0], $vars2));

							// Did we get a proper key? If not, we need to look in the next view
							if ($key)
							{
								$vars2[$view->key] = $key;
								array_shift($segments);

								// Found, go to the next segment
								continue;
							}
						}
						else
						{
							// The router is not complete. The get<View>Id() method is missing.
							return;
						}
					}

					// Add parent key
					if ($view->parent_key && isset($parentId))
					{
						$vars2[$view->parent_key] = $parentId;

						// Do not unset own key
						if ($view->key !== $view->parent->key)
						{
							unset($vars2[$view->parent->key]);
						}
					}

					if ($element !== $vars['view'])
					{
						// Key not found, jump to the next view
						break;
					}

					// Wrong URL
					return;
				}

				if (!$view->key)
				{
					if ($view->name === $segments[0])
					{
						// Add parent key
						if ($view->parent_key && isset($parentId))
						{
							$vars2[$view->parent_key] = $parentId;
							unset($vars2[$view->parent->key]);
						}

						array_shift($segments);

						// Found, jump to the next view
						break;
					}

					// Wrong URL
					return;
				}

				// Required for noIDs to get id from alias
				if (is_callable($func))
				{
					// If query has no key set, we assume 0.
					if (!isset($vars2[$view->key]))
					{
						$vars2[$view->key] = 0;
					}

					// Hand the data over to the router specific method and see if there is a content item that fits
					$key = call_user_func_array($func, array($segments[0], $vars2));

					if ($key)
					{
						// Add parent key
						if ($view->parent_key && isset($parentId))
						{
							$vars2[$view->parent_key] = $parentId;
							unset($vars2[$view->parent->key]);
						}

						$vars2[$view->key] = $key;
						array_shift($segments);

						// Found, jump to the next view
						break;
					}

					// Wrong URL
					return;
				}

				// The router is not complete. The get<View>Id() method is missing.
				return;
			}
		}

		// Copy all found variables
		$vars = $vars2;
	}

	/**
	 * Build a menu-less URL
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
		if (isset($query['Itemid']))
		{
			$item = $this->router->menu->getItem($query['Itemid']);

			if (!isset($query['option']) || ($item && $item->query['option'] === $query['option']))
			{
				return;
			}
		}

		// Get the path from the view of the current URL and parse it
		$path = array_reverse($this->router->getPath($query), true);

		// Check if specified view is known
		if (!isset($query['view'], $path[$query['view']]))
		{
			return;
		}

		// Get all views for this component
		$views = $this->router->getViews();

		$segments[] = $query['view'];

		// Requested view
		$mainView = $views[$query['view']];

		foreach ($mainView->path as $i => $element)
		{
			$view = $views[$element];
			$ids  = $path[$element];

			if ($element !== $query['view'] && $view->key)
			{
				$child = $views[$mainView->path[$i+1]];

				if ($child->nestable && $view->key === $child->key)
				{
					// Do not process this view, child will do it as they work on the same path elements
					continue;
				}
			}

			if ($ids)
			{
				if ($view->nestable)
				{
					// Remove 1:root
					array_pop($ids);

					foreach (array_reverse($ids, true) as $id => $segment)
					{
						$segments[] = str_replace(':', '-', $segment);
					}
				}
				elseif ($ids === true)
				{
					if ($element !== $query['view'])
					{
						$segments[] = $element;
					}
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

		// Remove key and view from query
		unset($query[$mainView->key], $query['view']);
	}
}
