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

function NewsfeedsBuildRoute(&$ARRAY)
{
	static $categories;
	$resolveNames = 0;

	// TODO: Resolve category names

	$parts = array();
	switch (@$ARRAY['view'])
	{
		case 'newsfeed':
			$parts[]	= 'feed';
			$parts[]	= @$ARRAY['feedid'];
			break;
		case 'category':
			$parts[]	= 'category';
			$parts[]	= @$ARRAY['catid'];
			break;
		default:
			// Do Nothing
			break;
	}

	if (isset( $ARRAY['limit'] ))
	{
		// Do all pages if limit = 0
		if ($ARRAY['limit'] == 0) {
			$parts[] = 'all';
		} else {
			$limit		= (int) $ARRAY['limit'];
			$limitstart	= (int) @$ARRAY['limitstart'];
			$page		= floor( $limitstart / $limit ) + 1;
			$parts[]	= 'page'.$page.'-'.$limit;
		}
	}

	//unset the whole array
	$ARRAY = array();

	return $parts;
}

function NewsfeedsParseRoute($ARRAY)
{
	// view is always the first element of the array
	$task	= array_shift($ARRAY);
	$nArray	= count($ARRAY);

	switch ($task)
	{
		case 'feed':
			JRequest::setVar('task', 'view', 'get');
			if (count($ARRAY)) {
 				$id = array_shift($ARRAY);
				JRequest::setVar('feedid', $id, 'get');
			}
			break;
		case 'category':
			JRequest::setVar('task', 'category', 'get');

			// Handle Pagination
			$last = @$ARRAY[$nArray-1];
			if ($last == 'all') {
				array_pop( $ARRAY );
				$nArray--;
				JRequest::setVar('limitstart', 0, 'get');
				JRequest::setVar('limit', 0, 'get');
				// if you want more than 1e6 on your page then you are nuts!
			} elseif (strpos( $last, 'page' ) === 0) {
				array_pop( $ARRAY );
				$nArray--;
				$pts		= explode( '-', $last );
				$limit		= @$pts[1];
				$limitstart	= (max( 1, intval( str_replace( 'page', '', $pts[0] ) ) ) - 1)  * $limit;
				JRequest::setVar('limit',$limit, 'get');
				JRequest::setVar('limitstart', $limitstart, 'get');
			}

			// Set the category id
			if (count($ARRAY)) {
 				$catid = array_shift($ARRAY);
				JRequest::setVar('catid', $catid, 'get');
			}
			break;
		default:
			break;
	}
}
?>