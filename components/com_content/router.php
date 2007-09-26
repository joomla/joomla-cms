<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

function ContentBuildRoute(&$query)
{
	$segments = array();
	
	if(isset($query['view'])) 
	{
		if(empty($query['Itemid'])) {
			$segments[] = $query['view'];
		} 
		
		unset($query['view']);
	};
	
	if(isset($query['catid'])) {
		$segments[] = $query['catid'];
		unset($query['catid']);
	};

	if(isset($query['id'])) {
		$segments[] = $query['id'];
		unset($query['id']);
	};

	if(isset($query['year'])) {
		
		if(!empty($query['Itemid'])) {
			$segments[] = $query['year'];
			unset($query['year']);
		}
	};

	if(isset($query['month'])) {
		
		if(!empty($query['Itemid'])) {
			$segments[] = $query['month'];
			unset($query['month']);
		}
	};
	
	if(isset($query['layout'])) 
	{	
		if(!empty($query['Itemid'])) {
			unset($query['layout']);
		}
	};
	
	return $segments;
}

function ContentParseRoute($segments)
{
	$vars = array();

	//Get the active menu item
	$menu =& JSite::getMenu();
	$item =& $menu->getActive();
	
	// Count route segments
	$count = count($segments);
	
	//Standard routing for articles
	if(!isset($item)) 
	{
		$vars['view']  = $segments[0];
		$vars['id']    = $segments[$count - 1];
		return $vars;
	}
	
	//Handle View and Identifier
	switch($item->query['view'])
	{
		case 'section' :
		{
			if($count == 1) {

				if(isset($item->query['layout']) && $item->query['layout'] == 'blog') {
					$vars['view'] = 'article';
				} else {
					$vars['view'] = 'category';
				}
			}

			if($count == 2) {
				$vars['view']  = 'article';
				$vars['catid'] = $segments[$count-2];
			}

			$vars['id']    = $segments[$count-1];

		} break;

		case 'category'   :
		{
			$vars['id']   = $segments[$count-1];
			$vars['view'] = 'article';
		
		} break;

		case 'frontpage'   :
		{
			$vars['id']   = $segments[$count-1];
			$vars['view'] = 'article';

		} break;

		case 'article' :
		{
			$vars['id']	  = $segments[$count-1];
			$vars['view'] = 'article';
		} break;
		
		case 'archive' :
		{
			$vars['year']  = $segments[$count-2];
			$vars['month'] = $segments[$count-1];
			$vars['view']  = 'archive';
		}	
	}

	return $vars;
}
?>