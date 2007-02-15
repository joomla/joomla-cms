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
	if(isset($query['view'])) {
		$segments[] = $query['view'];
		unset($query['view']);
	};

	if(isset($query['layout'])) {
		$segments[] = $query['layout'];
		unset($query['layout']);
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
		$segments[] = $query['year'];
		unset($query['year']);
	};

	if(isset($query['month'])) {
		$segments[] = $query['month'];
		unset($query['month']);
	};

	return $segments;
}

function ContentParseRoute($segments)
{
	global $mainframe;
	
	//Get the router
	$router =& $mainframe->getRouter();
	
	// view is always the first element of the array
	$view = array_shift($segments);
	JRequest::setVar('view', $view, 'get');

	$next = array_shift($segments);

	switch($view)
	{
		case 'article'  :
		case 'category' :
		case 'section'  :
		{
			if(is_numeric((int)$next) && ((int)$next != 0)) {
				$router->setVar('id', (int)$next);
			}
			else
			{
				$router->setVar('layout', $next);
				$router->setVar('id', (int)array_shift($segments));
			}
		} break;

		case 'archive'   :
		{
			if(is_numeric((int)$next) && ((int)$next != 0)) {
				$router->setVar('year', $next);
				$router->setVar('month', array_shift($segments));
			}
			else
			{
				$router->setVar('layout', $next);
				$router->setVar('year', array_shift($segments));
				$router->setVar('month', array_shift($segments));
			}
		} break;
	}
}
?>