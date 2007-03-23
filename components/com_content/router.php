<?php
/**
* @version		$Id: sef.php 5747 2006-11-12 21:49:30Z louis $
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
	
	if(isset($query['catid'])) {
		$segments[] = $query['catid'];
		unset($query['catid']);
	};

	if(isset($query['id'])) {
		$segments[] = $query['id'];
		unset($query['id']);
	};

	if(isset($query['year'])) {
		$segments[] = $query['year'];
		unset($query['year']);
	};

	if(isset($query['month'])) {
		$segments[] = $query['month'];
		unset($query['month']);
	};
	
	unset($query['view']);

	return $segments;
}

function ContentParseRoute($segments)
{
	//Get the active menu item
	$menu =& JMenu::getInstance();
	$item =& $menu->getActive();

	// Count route segments
	$count = count($segments);

	//Handle View and Identifier
	switch($item->query['view'])
	{
		case 'section' :
		{
			if($count == 1) {
				$view = 'category';
			}

			if($count == 2) {
				$view = 'article';
			}

			$id = $segments[$count-1];

		} break;

		case 'category'   :
		{
			$id   = $segments[$count-1];
			$view = 'article';

		} break;
		
		case 'frontpage'   :
		{
			$id   = $segments[$count-1];
			$view = 'article';

		} break;

		case 'article' :
		{
			$id	= $segments[$count-1];
			$view	= 'article';
		} break;
	}

	JRequest::setVar('view', $view, 'get');
	JRequest::setVar('id'  , $id, 'get');
}
?>