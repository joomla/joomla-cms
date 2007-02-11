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

function WeblinksBuildRoute(&$ARRAY)
{
	$parts = array();
	if(isset($ARRAY['catid'])) {
		$parts[] = $ARRAY['catid'];
	};

	if(isset($ARRAY['id'])) {
		$parts[] = $ARRAY['id'];
	};


	if (isset( $ARRAY['limit'] ))
	{
		// Do all pages if limit = 0
		if ($ARRAY['limit'] == 0) {
			$parts[] = 'all';
		} else {
			$limit		= (int) $ARRAY['limit'];
			$limitstart	= (int) @$ARRAY['limitstart'];
			$page		= floor( $limitstart / $limit ) + 1;
			$parts[]	= 'page'.$page.':'.$limit;
		}
	}

	//unset the whole array
	$ARRAY = array();
	return $parts;
}

function WeblinksParseRoute($ARRAY)
{
	$menu =& JMenu::getInstance();
	$item =& $menu->getActive();

	// Handle Pagination
	$nArray = count($ARRAY);
	$last = @$ARRAY[$nArray-1];
	if ($last == 'all')
	{
		array_pop( $ARRAY );
		$nArray--;
		$limit      = 0;
		$limitstart = 0;
		JRequest::setVar('limit', $limit, 'get');
		JRequest::setVar('limitstart', $limitstart, 'get');
	}
	elseif (strpos( $last, 'page' ) === 0)
	{
		array_pop( $ARRAY );
		$nArray--;
		$pts		= explode( ':', $last );
		$limit		= @$pts[1];
		$limitstart	= (max( 1, intval( str_replace( 'page', '', $pts[0] ) ) ) - 1)  * $limit;
		JRequest::setVar('limit', $limit, 'get');
		JRequest::setVar('limitstart', $limitstart, 'get');
	}

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

			$id = $ARRAY[$nArray-1];

		} break;

		case 'category'   :
		{
			$id   = $ARRAY[$nArray-1];
			$view = 'weblink';

		} break;
	}

	JRequest::setVar('view', $view, 'get');
	JRequest::setVar('id', (int)$id, 'get');
}
?>