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

function WeblinksBuildRoute(&$query)
{
	$parts = array();
	
	if(isset($query['catid'])) 
	{
		$parts[] = $query['catid'];
		unset($query['catid']);
	};

	if(isset($query['id'])) 
	{
		$parts[] = $query['id'];
		unset($query['id']);
	};
	
	unset($query['view']);

	return $parts;
}

function WeblinksParseRoute($parts)
{
	$menu =& JMenu::getInstance();
	$item =& $menu->getActive();

	// Count route parts
	$nArray = count($parts);
	
	//Handle View and Identifier
	switch($item->query['view'])
	{
		case 'categories' :
		{
			if($nArray == 1) {
				$view = 'category';
			}

			if($nArray == 2) {
				$view = 'weblink';
			}

			$id = $parts[$nArray-1];

		} break;

		case 'category'   :
		{
			$id   = $parts[$nArray-1];
			$view = 'weblink';

		} break;
	}

	JRequest::setVar('view', $view, 'get');
	JRequest::setVar('id', (int)$id, 'get');
}
?>