<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Rule for the default handling of component routing
 *
 * @since  3.4
 */
class JComponentRouterRulesDefault implements JComponentRouterRulesInterface
{
	/**
	 * Router this rule belongs to
	 *
	 * @var JComponentRouterAdvanced
	 * @since 3.4
	 */
	protected $router;

	/**
	 * Class constructor.
	 *
	 * @param   JComponentRouterAdvanced  $router  Router this rule belongs to
	 *
	 * @since   3.4
	 */
	public function __construct(JComponentRouterAdvanced $router)
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
		$views = $this->router->getViews();
		$active = $this->router->menu->getActive();
		$cviews = $views[$active->query['view']]->children;

		$vars = array_merge($active->query, $vars);

		$nestable = false;
		foreach ($segments as $segment) {
			$found = false;

			@list($id, $alias) = explode('-', $segment, 2);
			foreach($cviews as $cview) {
				if(isset($cview->key) && (int) $id > 0) {
					$found = true;
					if($cview->nestable) {
						$item = call_user_func(array($this->router, 'get'.ucfirst($cview->name)), $id);
						if($item->alias != $alias) {
							$found = false;
							$cviews = array_merge($cviews, $cview->children);
							continue;
						}
						$nestable = true;
					}
					$vars['view'] = $cview->name;
					if(isset($cview->parent->key) && isset($vars[$cview->parent->key])) {
						$vars[$cview->parent_key] = $vars[$cview->parent->key];
					}

					if ($alias)
					{
						$vars[$cview->key] = $id.':'.$alias;
					}
					else
					{
						$vars[$cview->key] = $id;
					}
				}
				elseif ($cview->name == $segment)
				{
					$vars['view'] = $cview->view;

					$found = true;
					break;
				}
			}
			if ($found)
			{
				if (!$nestable)
				{
					if (!isset($cviews->children))
					{
						break;
					}
					$cviews = $cviews->children;
				}
				$nestable = false;
			}
			else
			{
				break;
			}
		}
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
		//Get the menu item belonging to the Itemid that has been found
		$item = $this->router->menu->getItem($query['Itemid']);

		if (!isset($query['view']))
		{
			return;
		}

		//Get all views for this component
		$views = $this->router->getViews();

		//Return directly when the URL of the Itemid is identical with the URL to build
		if(isset($item->query['view']) && isset($query['view']) && $item->query['view'] == $query['view']) {
			$view = $views[$query['view']];
			if(isset($item->query[$view->key]) && $item->query[$view->key] == (int) $query[$view->key]) {
				unset($query[$view->key]);
				$view = $view->parent;
				while($view) {
					unset($query[$view->child_key]);
					$view = $view->parent;
				}
				unset($query['view']);
				unset($query['layout']);
				return;
			}
		}

		//get the path from the view of the current URL and parse it to the menu item
		$path = array_reverse($this->router->getPath($query));
		$found = false;
		$found2 = false;
		for($i = 0, $j = count($path); $i < $j; $i++) {
			reset($path);
			$view = key($path);
			if($found) {
				$ids = array_shift($path);
				if($views[$view]->nestable) {
					foreach(array_reverse($ids) as $id) {
						if($found2) {
							$segments[] = str_replace(':', '-', $id);
						} else {
							if((int) $item->query[$views[$view]->key] == (int) $id)
							{
								$found2 = true;
							}
						}
					}
				} else {
					if(is_bool($ids)) {
						$segments[] = $views[$view]->name;
					} else {
						$segments[] = str_replace(':', '-', $ids[0]);
					}
				}
			} else {
				if($item->query['view'] != $view) {
					array_shift($path);
				} else {
					if(!$views[$view]->nestable) {
						array_shift($path);
					} else {
						$i--;
						$found2 = false;
					}
					$found = true;
				}
			}
			unset($query[$views[$view]->child_key]);
		}
		unset($query['layout']);
		unset($query[$views[$query['view']]->key]);
		unset($query['view']);
	}
}
