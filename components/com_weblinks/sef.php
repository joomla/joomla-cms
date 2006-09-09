<?php
/**
* @version $Id: joomla.sefurlbot.php 4704 2006-08-24 04:16:59Z webImagery $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

function weblinks_buildURL($ARRAY, &$params)
{
	static $categories;
	$resolveNames = $params->get('realnames',0);

	// TODO: Resolve category names

	$parts = array();
	switch (@$ARRAY['task'])
	{
		case 'new':
			$parts[]	= 'new';
			break;
		case 'save':
			$parts[]	= 'save';
			break;
		case 'cancel':
			$parts[]	= 'cancel';
			break;
		case 'view':
			$parts[]	= 'view';
			$id			= @$ARRAY['id'];
			$parts[]	= $id;
			break;
		case 'category':
			$parts[]	= 'category';
			$id			= @$ARRAY['catid'];
			$parts[]	= $id;
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
	$string = implode('/', $parts);
	return ($string)?$string.'/':null;
}

function weblinks_parseURL($ARRAY, &$params)
{
	// view is always the first element of the array
	$task	= array_shift($ARRAY);
	$nArray	= count($ARRAY);

	switch ($task)
	{
		case 'new':
			JRequest::setVar('task', 'new', 'get');
			break;
		case 'save':
			JRequest::setVar('task', 'save', 'get');
			break;
		case 'cancel':
			JRequest::setVar('task', 'cancel', 'get');
			break;
		case 'view':
			JRequest::setVar('task', 'view', 'get');
			if (count($ARRAY)) {
 				$id = array_shift($ARRAY);
				JRequest::setVar('id', $id, 'get');
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