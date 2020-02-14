<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2019 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('Restricted access');

class SppagebuilderRouter extends JComponentRouterBase {

	public function build(&$query) {
		$app = JFactory::getApplication();
		$menu = $app->getMenu();

		$segments = array();

		if (empty($query['Itemid'])) {
			$menuItem = $menu->getActive();
			$menuItemGiven = false;
		} else {
			$menuItem = $menu->getItem($query['Itemid']);
			$menuItemGiven = true;
		}

		// Check again
		if ($menuItemGiven && isset($menuItem) && $menuItem->component != 'com_sppagebuilder') {
			$menuItemGiven = false;
			unset($query['Itemid']);
		}

		if (isset($query['view'])) {
			$view = $query['view'];
		} else {
			return $segments;
		}

		if (($menuItem instanceof stdClass) && $menuItem->query['view'] == $query['view']) {
			if (!$menuItemGiven) {
				$segments[] = $view;
			}
			unset($query['view']);
		}

		if($menuItemGiven) {
			
			if(isset($query['view']) && $query['view']) {
				unset($query['view']);
			}
			$id = 0;
			if(isset($query['id']) && $query['id']) {
				$id = $query['id'];
				unset($query['id']);
			}

			if(isset($query['tmpl']) && $query['tmpl']) {
				unset($query['tmpl']);
			}

			if(isset($query['layout']) && $query['layout']) {
				$segments[] = $query['layout'];
				if($id) {
					$segments[] = $id;
				}
				unset($query['layout']);
			}
		}

		return $segments;
	}

	// Parse
	public function parse(&$segments) {
		$app = JFactory::getApplication();
		$menu = $app->getMenu();
		$item = $menu->getActive();
		$total = count((array) $segments);
		$vars = array();
		$view = (isset($item->query['view']) && $item->query['view']) ? $item->query['view'] : '';

		if(count($segments) == 2 && $segments[0] == 'edit') {
			$vars['view'] = 'form';
			$vars['id'] = (int) $segments[1];
			$vars['tmpl'] = 'component';
			$vars['layout'] = 'edit';
			return $vars;
		}

		return $vars;
	}
}

function SppagebuilderBuildRoute(&$query) {
	$router = new SppagebuilderRouter;
	return $router->build($query);
}

function SppagebuilderParseRoute($segments) {
	$router = new SppagebuilderRouter;
	return $router->parse($query);
}
